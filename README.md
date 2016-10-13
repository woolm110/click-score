# Click Score

> Create a dynamic image displaying a percentage of how many times a given link has been clicked. Uses the Mailchimp API

## Getting started
- Upload files to server
- Create an API key on Mailchimp.com Instructions [here](http://kb.mailchimp.com/integrations/api-integrations/about-api-keys)

## Usage
- Add a unique linkID to any links in the email you wish to track. e.g. http://example.com?linkID=test-link-1
- After your email has been broadcast go to the [Mailchimp Playground](https://us1.api.mailchimp.com/playground/) and enter your API key
- Click reports, find your recently broadcast email and click sub-resources > click details and locate the email ID at the top
- Open config/tracking.json and create an object for your email campaign, give it a unique name and paste in the email ID
- Open index.php and enter your API key. e.g `new LinkClickToPercentage('API KEY GOES HERE')`
- Go to `http://[SERVER_ADDR]/[CLICK_SCORE_FOLDER]/?emailName=[EMAIL_NAME]&linkId=[LINK_ID]`to view the image. e.g. http://mydomain.com/click-score/?emailName=clickScoreTest&linkId=test-link-2

## Styles
Any new style can be added to styles/styles.json. Create a new style and reference the name within config/tracking.json to use that style

## Example uses
Create a dynamic poll within an email. Each time a link is clicked the image showing the click percentage will be updated next time the email is reloaded. 
