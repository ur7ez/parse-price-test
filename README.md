Implement a service that allows to monitor the price change of an advert on OLX:
1. The service must provide HTTP method for subscribing to price changes. Method receives a link to the ad and an email address to which to send a message.
2. After successful subscription, the service must monitor the price of the ad and send a message to the specified email address (in case of a price change).
3. If several users have subscribed to the same ad, the service should not check the ad price again.

The results should include:
- A diagram of the service and its brief description
- A link to the code repository
- Subscription to price changes
- Tracking price changes
- Sending a message to the subscriber's e-mail
- Programming language - PHP

If several implementation options appeared during the task, describe the advantages and disadvantages of each of them. Indicate why you chose one or another option.

To get the price of an ad, you can:
- parse the ad web page
- independently analyze the traffic on mobile applications or a mobile version of website and find out what API is uded there to get information about the ad

**Complications:**
- Implement a full-fledged service that solves the task (the service must run in a Docker container).
- Write tests (try to achieve 70% coverage or more).
- User email confirmation.
