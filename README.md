# LaraWall

[![Version](https://poser.pugx.org/icemont/larawall/version)](//packagist.org/packages/icemont/larawall)
[![License](https://poser.pugx.org/icemont/larawall/license)](//packagist.org/packages/icemont/larawall)

LaraWall - web panel to manage access to service ports of a server group.

## About LaraWall
LaraWall is a control panel for managing access to service ports on a large group of servers from a single point. LaraWall is based on Laravel PHP framework and [Z-song admin panel generator](https://github.com/z-song/laravel-admin).

Can be used, for example, to control access to different services (each service is a different port on a different server) for a lot of customers and a group of servers. Service ports can be bundled into packages, and customers can be subscribed to these packages. Subscriptions can have an expiration date.
You can also change the status for each entity separately, e.g. to disable a subscription for a certain package for all customers, you don't have to disable every subscription separately, just disable the package. Also, for example, you can disable an individual service port or server with all ports at once, so you do not have to remove it from all packages separately.

**Implemented entities:**
- "Servers" that have the "Service ports" entity
- "Packets", which contain the "Service Ports" entities
- "Users", which have the entity "IP addresses of users"
- "Subscriptions" - binding "Packets" to "Users"

The relationships are shown in more detail in the diagram:
![](https://raw.githubusercontent.com/Icemont/larawall-docs/main/assets/images/db-diagram.png "Diagram")

## Handlers for servers and API
**List of available handlers for servers:**
- [Handler for the Linux netfilter firewall](https://github.com/Icemont/LaraWall-Iptables-Handler) (uses iptables and ipset utilities)

📌 API endpoint with server data (JSON) for the handler: 
`https://your_project_host/api/server/data`

For authorization is used IP address of the server. For example, you created a server with IP address 10.0.0.10 in the panel. When this server contacts API endpoint `/api/server/data`, the system will authorize it by its IP address (external IP address from which the server sends a request to API should be the same as the server in the panel) and send in response data in JSON to manage firewall rules on the server with the handler.

## Installation

	$ composer create-project icemont/larawall

After installation and basic configuration, run these command to publish "laravel-admin" assets：

	$ php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"


Perform migrations to create a database tables structure:

	$ php artisan migrate


Then import to DB menu structure data of the admin panel:

    $ php artisan db:seed --class=AdminTablesSeeder

In the next step, create a user for authorization in the administrative area of the panel:

    $ php artisan admin:create-user

To access the admin panel, go to `https://your_project_host/admin`

📌 You need to configure the Laravel task scheduler to change the status of subscriptions when they expire. Refer to the [official documentation](https://laravel.com/docs/8.x/scheduling#running-the-scheduler) for instructions.

📌 Installation, configuration, and deployment are basically the same as a typical Laravel-based project, so for detailed instructions you can refer to the official documentation on Laravel framework [installation](https://laravel.com/docs/8.x/installation) and [deployment](https://laravel.com/docs/8.x/deployment).

## Demo
Panel delivered with a demo data generator. To generate demo data, use command:

    $ php artisan db:seed

## Contact

Open an issue on GitHub if you have any problems or suggestions.

## License

The contents of this repository is released under the [MIT license](https://opensource.org/licenses/MIT).
