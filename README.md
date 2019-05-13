# share2linkedin
Share posts to LinkedIn by using the API of LinkedIn

I created these .PHP-files with a Drupal-site. I use a cron-job to call the page to schedule a daily check if there are new Drupal-posts. If so, share it with LinkedIn.

To use it you need to create an application with your LinkedIn-account. Visit https://developer.linkedin.com/ and then click on My Apps (after logging in). Create a new app with the rights r_basicprofile, r_emailaddress and w_share. At the oAuth2 redirect URL fill the URL of the place where you will run the /callback/index.php file.

A complete story of my configuration can be found on https://techblog.dirkhornstra.nl/node/22 the article is in Dutch, but the links in the article should show you the way.
