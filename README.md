# W.S.G. Isaac Newton - HTTP REST API
This system has 2 goals:
- Provide a internal use, single access point for data that is shared between different, independent apps.
- Expose endpoints that can be used for integration with 3rd party apps, notably webhooks.

## Architecture

### Database
The software requires a MySQL/MariaDB database with an InnoDB engine. Below, you can find the SQL queries to create the tables per domain. The software is tested with MariaDB 10. Other DBMS's may or may not work, but this is not and will not be tested nor supported.

### Authentication and authorization
The system requires clients to authenticate themselves. This is currently always done with HTTP Basic Authentication.

#### Database migrations
```sql
CREATE TABLE `basic_auth` (
	`scope` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`password_hash` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`active` BIT(1) NOT NULL DEFAULT b'0',
	PRIMARY KEY (`scope`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
```

### Webhooks
Congressus provides webhook integration such that they will make a webhook call to your registered endpoint, whenever certain changes happen in the administration. This is used to push information relating to the Directory and Member domains. More integration can easily be added in the future.

## Domains
### Congressus Webhooks
Method | URI
-|-
`POST` | /webhooks/congressus/group
`POST` | /webhooks/congressus/member/birthday

### Directory
More specifically an LDAP directory, however the system is protocol-agnostic. The directory is used for the 'active member administration' that is built on top of the Congressus administration. Note: this domain has a dependency on Congressus Webhooks.

Method | URI
-|-
`GET` | /directory/event-queue
`POST` | /directory/event-queue/pop
`PUT` | /directory/event-queue/{id}/mark

#### Database migrations
```sql
CREATE TABLE `directory_event_queue` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`event_status` ENUM('pending','processing','processed','failed') NOT NULL DEFAULT 'pending' COLLATE 'utf8mb4_unicode_ci',
	`event_trigger` ENUM('group_added','group_updated','group_deleted','group_membership_added','group_membership_updated','group_membership_deleted') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`group_id` INT(11) NOT NULL,
	`group_name` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`group_breadcrumbs` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`member_id` INT(11) NULL DEFAULT NULL,
	`created_at` DATETIME NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
```

### Members
In reality this is just the members who have their birthday today, but `Members` is a more extensible domain than `Birthdays`. Note: this domain has a dependency on Congressus Webhooks.

Method | URI
-|-
`GET` | /members/todays-birthdays

#### Database migrations
```sql
CREATE TABLE `birthdays` (
	`congressus_member_id` INT(11) NOT NULL,
	`date_of_birth` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`congressus_member_id`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;
```

### Polls
Currently only used for queries the active polls. Should be extended to allow full CRUD over HTTP. Currently, the producers operate directly on the DB, but this is incredibly inflexible.

Method | URI
-|-
`GET` | /polls/active

### Puzzles
Puzzle assets can be downloaded from here. Puzzles are currently uploaded by manually copying the assets into `/var/puzzles/`.

Method | URI
-|-
`GET` | /puzzles
`GET` | /puzzles/{filename}
`GET` | /puzzles-advertisement

#### Cron job
Make sure there is a cron job that runs /bin/schedule at least once a day at the start of the day. This job is needed to trigger archiving of old puzzles and creation of new folders.
