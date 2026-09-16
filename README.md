# Endpoints
Method | URI
-|-
`POST` | /webhooks/congressus/member/birthday
`POST` | /webhooks/congressus/group
`GET` | /directory/event-queue
`POST` | /directory/event-queue/pop
`PUT` | /directory/event-queue/{id}/mark
`GET` | /members/todays-birthdays
`GET` | /polls/active

# Database migrations
The software requires a MySQL/MariaDB database with an InnoDB engine. Below, you can find the SQL queries to create the tables. Other DBMS's may or may not work, but this is not and will not be tested nor supported.

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
