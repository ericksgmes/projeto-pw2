# Restaurante Webservice - PW2 Discipline Project

This repository contains the webservice developed for the **Programação para Web 2 (PW2)** discipline. It provides endpoints for managing a restaurant's functionality, such as handling employees, orders, tables, and payments.

## Getting Started

Follow these instructions to set up and run the project using **XAMPP** and **Postman**.

### Prerequisites

- **XAMPP** (for Apache and MySQL servers)
- **Postman** (for testing API endpoints)
- **Composer** (for managing PHP dependencies)

### How to Run

1. **Install Dependencies:**
   - Run `composer install` in the project root directory to install PHP dependencies.

2. **Start Servers:**
   - Open **XAMPP** and start the **MySQL** and **Apache** servers.

3. **Create Database:**
   - Open **phpMyAdmin** by navigating to `http://localhost/phpmyadmin`.
   - Create a new database called **`restaurante`**.
   - Import the **`init.sql`** file located in the repository to set up the initial schema and data.

4. **Test Endpoints:**
   - Open **Postman**.
   - Use the routes described in the **`rotas.html`** file to test the available endpoints of the webservice.

### Accessing Swagger Documentation

The project includes Swagger UI for easy visualization and testing of the API. To access the Swagger documentation:

1. Ensure that the **Apache** server is running in **XAMPP**.
2. Navigate to `http://localhost/resturante-webservice/swagger-ui/dist` in your web browser.
3. The Swagger interface will provide detailed documentation of all available endpoints and allow you to test them directly from the browser.

### Available Endpoints

The endpoints provide CRUD operations for managing various aspects of the restaurant, such as:

- **Employees**: Add, update, delete, and retrieve employee information.
- **Tables**: Manage table status and assignments.
- **Orders**: Create and update orders for customers.
- **Payments**: Handle payment processing for completed orders.

For detailed route information, please refer to the **`rotas.html`** file.

### Directory Structure

```
├── config/            # Configuration files (database connection, utilities)
├── controller/        # Controllers for handling requests
├── frontend/          # Frontend files (not yet integrated)
├── model/             # Database models for different entities
├── swagger-ui/        # Swagger UI files for API documentation
├── vendor/            # Composer dependencies
├── index.php          # Entry point for the webservice
├── rotas.html         # Documentation for available API routes
├── LICENSE            # Project license
└── README.md          # Project documentation
```

### Notes

- **Frontend**: The frontend is currently **not wired** to the backend. Only the backend API is available for interaction.
- **API Testing**: Use **Postman** or a similar tool to interact with the REST API for testing and development purposes.

### Future Improvements

- **Frontend Integration**: Connect the frontend to the backend to provide a complete user experience.
- **Authentication**: Implement user authentication to secure API endpoints.

### Authors

- Developed with ❤️ by [@ericksgmes](https://github.com/ericksgmes) and [@leoh3nrique](https://github.com/leoh3nrique)

### License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

### Additional Resources

- **XAMPP**: [Download XAMPP](https://www.apachefriends.org/index.html)
- **Postman**: [Download Postman](https://www.postman.com/downloads/)
- **Composer**: [Download Composer](https://getcomposer.org/download/)

Feel free to contribute and open issues to improve this project!
