# ADMS (Attendance Device Management System)

ADMS is a comprehensive Attendance Device Management System designed to handle biometric and access control data from various devices. This system is built using Laravel, a PHP framework, provides functionalities to store, manage user and fingerprint data.

## Features

- Fingerprint data storage
- Device status monitoring

## Screenshots
Device Connected
![App Screenshot](https://github.com/saifulcoder/adms-server-ZKTeco/blob/main/Screenshot_7.png)
Attendance Recorded
![App Screenshot](https://github.com/saifulcoder/adms-server-ZKTeco/blob/main/Screenshot_8.png)
Device Log
![App Screenshot](https://github.com/saifulcoder/adms-server-ZKTeco/blob/main/Screenshot_9.png)
Attendence Log
![App Screenshot](https://github.com/saifulcoder/adms-server-ZKTeco/blob/main/Screenshot_10.png)

## Installation

### Prerequisites

Before you begin, ensure you have the following installed on your system:

- PHP >= 8.0
- Composer
- MySQL or any other supported database
- Web server (Apache, Nginx, etc.)

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/saifulcoder/adms-server-ZKTeco.git adms-server
   cd adms-server
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Copy the `.env` file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Configure the `.env` file**
   Open the `.env` file and set your database credentials and other environment variables:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=adms
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run the migrations**
   ```bash
   php artisan migrate
   ```

7. **Serve the application**
   ```bash
   php artisan serve
   ```

### Monitoring Device Status

You can monitor the status of devices by querying the `devices` table where the `online` field indicates the last time the device was online.

## Postman Collection

For testing and interacting with the API endpoints, you can use the provided Postman collection:
[Postman Collection](https://github.com/saifulcoder/adms-server-ZKTeco/blob/main/ADMS server ZKTeco.postman_collection.json)


## Authors

- [@saifulcoder](https://github.com/saifulcoder)

## For Improvement and project

contact us saiful.coder@gmail.com

## Contributing

This project helps you and you want to help keep it going? Buy me a coffee:
<br> <a href="https://www.buymeacoffee.com/saifulcoder" target="_blank"><img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 61px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;" ></a><br>
or via <br>
<a href="https://saweria.co/saifulcoder">https://saweria.co/saifulcoder</a>

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.