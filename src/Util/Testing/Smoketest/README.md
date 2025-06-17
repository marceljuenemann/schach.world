### Usage of the Smoketest

Create a class that implements SmoketestInterface.php. It provides the URLs that are checked with cURL
for HTTP Status and if there are errors, for error message. All successfully checked URLs and errors are written to var/log/smoketest.log
The process uses Symfony Messenger to do the cURL Requests, since these can take a while.

#### Class that provides the urls
The class that implements SmoketestInterface.php returns an array of URLs. This could be /statistik for all Divisions, like src/League/Testing/Smoketest/StatisticsSmoketest does. When you have written your class it can be selected in the console command.

#### Console Command
The command src/Util/Command/ExecuteSmoketestCommand.php is called with

~~~
console testing:execute-smoketest
~~~

When executing this command, you are given a select for all the class names that implement SmoketestInterface. Select your class. All URLs returned by your class are dispatched as Messages to async.

#### Messenger config
The dispatched messages are set to use an async transport for the test to be able to process larger quantities of URLs. It is configured in config/packages/messenger.yaml.
You need to enable the doctrine transport in .env or .env.local. Sample config is given outcommented in .env, outcomment the line

~~~
MESSENGER_TRANSPORT_DSN=doctrine://webapp
~~~

to use the doctrine transport. 
Messages are then written into the database set as WEBAPP_DATABASE_URL. I could not use the LEAGUE_DATABASE_URL since it has a mix of latin and UTF8 encoding.


### Consume the messages
In a second terminal window, run

~~~
console messenger:consume
~~~

This starts the doctrine transport, which sends the messages one by one to src/Util/Command/MessageHandler/SmoketestMessageHandler. If you have a lot of URLS (e.g. /statistics for all divisions are ca 900) this can take a few minutes. Using Messenger and the doctrine transport avoids a PHP timeout.
The SmoketestMessageHandler checks the URLs and writes the result to smoketest.log
The handler also extracts the error message. This only works in DEV mode, since Symfony provides an error message in the HTML title tag when set to show all errors.


