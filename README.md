## About Booking App:

Booking App is an app that helps create different services such as Hair Curt services and helps create a bookable calendar which can be booked by clients.

### Setting Up:

- composer install
-  php artisan migrate -seed //will seed Men Haircut and Women Haircut service with its calendar configuration

### Routes:

- Route GET /api/bookable-schedules Gets list of services with their bookable calendar
- Route POST /bookings/services/{service id} Gets list of services with their bookable calendar

### Testing:

It is better if you have a testing environment set up since it uses RefreshDatabase and it will wipe out the data. So add .env.testing file and configure your testing database

- php artisan test
