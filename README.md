# Drupal Chef bot

## Installation instructions

This bot currently needs to be set up and provided slack tokens on a workspace level.  The following environmental variables need to be defined after installing the bot in slack manually.

DATABASE_URL
SLACK_APP_ID
SLACK_CLIENT_ID
SLACK_CLIENT_SECRET
SLACK_SIGNING_SECRET
SLACK_VERIFICATION_TOKEN
SLACK_OAUTH_TOKEN
SLACK_BOT_OAUTH_TOKEN
SLACK_BOT_NAME

run 

`composer install`
to install dependencies, then 

`bin/console doctrine:migrations:migrate`
to install the database schema.  The bot's event endpoint should be set to /api/event and appropiate permissions given.