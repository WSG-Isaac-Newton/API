# Puzzle folder

This is the puzzle folder. Here you have access to everything you need to upload today's puzzles, set the advertisement image, and access old puzzles. You can modify quite a lot here, but I urge you not to change anything OUTSIDE this folder, because then you might cause this server to stop working correctly.

## Uploading today's puzzle
In this folder, you should see a folder with today's date as its name, formatted as `YYYY-MM-DD`. Place the .png files of the puzzle screenshots in here. Make sure that they have meaningful names, because the file names are used for the buttons on the touchscreen.

Example: Give a screenshot of a sudoku the name `Sudoku.png`, and not something like `Screenshot 2026-09-22 234608.png`

### Is there no folder for today?
Create it yourself and see if placing the screenshots in it still works. Make sure it is formatted as `YYYY-MM-DD`. Contact a developer if that doesn't work or the folder is missing regularly.

## Changing the advertisement image (sponsor logo)
You can store advertisement images in the `advertisements/` folder. This folder is intended to store multiple images, to easily change which image is shown on the puzzle paper. Which file is shown is configured in the file `config.ini`. Here you can find the `PUZZLE_ADVERTISEMENT_FILE` setting. You can change which image is shown by changing this setting to the name of the image.
