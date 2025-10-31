# Restaurant Challenge API

The application runs on top of Laravel 9, PHP 8.2 and MySql 8.0.

### Running the API locally with Sail and Docker (macOS and Linux)

Make sure [Docker Desktop](https://www.docker.com/products/docker-desktop/) is installed and clone the project to your local machine. Once you have it, navigate to the project directory, run composer and start [Laravel Sail](https://laravel.com/docs/10.x/sail).

```
git clone https://github.com/fadaa-tech/Restaurant-Challenge Restaurant Challenge API

cd Restaurant-Challenge

cp .env.example .env

composer install

./vendor/bin/sail up
```

At this point you should have a running API and be able to start developing.

The first time the MySQL container starts, it will create two databases. One is called `laravel` and is there to support your local development. The other one is a dedicated testing database named `testing` and will ensure that your tests do not interfere with your development data.

Please refer to [Laravel Sail](https://laravel.com/docs/11.x/sail) documentation to learn more about how to interact with the application artisan commands, database, tests, etc.

# Introduction

This challenge assumes the challenged developer to be part of an outsorced team of engineers, working on a new project, that will later be handover to our internal teams. 
Please consider the following.

- Please create one PR for each challenge, and only focus on the presented problem on each PR. Please don't duplicate code between PR's, they should not depend on each other.
- Please assume all existing code and it's formatting and style are the expected code standards and follow them.
- Please assume that all branches naming and conventions are the expected one and follow them.
- Please document your PRs and help us understand the proposed changes. PR description should explain, what, why and how.
- All suggested code must be covered with Unit tests.

# The Challenge (part I)

Your team is developing a sophisticated restaurant management system to streamline operations for various food establishments. This system encompasses modules for order management:

* inventory tracking, customers, and financial transactions. As part of this system, the OrderService class was designed to handle the order placement and associated tasks such as order validation,
* calculation of order details, processing payments, notifying customers, sending invoices, and managing inventory, seamlessly.

Initially, we created the OrderService class to be responsible for doing the Job, but later it became a monolithic entity, making it difficult to maintain and scale as the system evolves.
Your task is to analyze the provided OrderService class, identify the issues and challenges outlined above, and propose solutions to address each issue.

* You should refactor the code to adhere to SOLID principles.
* Please open one PR (or more) with the proposed solution.

# The Challenge (part II)

We need to report our daily revenue to an external service called Reporting Service by scheduling the SendTotalRevenueReportJob to run daily. Creating this report involves interacting with the external API through a sequence of three different HTTP requests.

Currently, we face a challenge where one of these requests frequently fails randomly. When this happens, the job fails and is released back to the queue. When retrying, all three requests are triggered again, leading to unexpected results and additional charges from the external service.

Additionally, we sometimes hit our hourly quota for submitting reports, mostly due to these failures and retries.

Another issue we've observed is that generating the report results in an unexpectedly high number of database queries.

Your task is to open a PR (or multiple PRs) with a solution to achieve the following:

* Rework the necessary code to achieve an overall robust and efficient process.
* The job should not re-trigger all requests upon retry but instead retry only from the failed request.
* The job should be resilient, handling retries efficiently, and know when not to retry.


# The Challenge (part III)

Finally, we need to implement the Create Orders API. An important requirement is that this API must limit or throttle the number of requests it accepts per minute, and this limit should apply separately for each branch.
Your task is to open a PR with a solution to achieve this outcome. Focus on the following:

* Code quality, project standards, best practices, and test coverage.
* Providing a descriptive PR that includes enough information to support reviewers in their work and serve as a future reference.

Good luck!
