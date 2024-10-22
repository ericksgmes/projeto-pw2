# Restaurante Webservice - PW2 Discipline Project

This repository contains the webservice developed for the **Programação para Web 2 (PW2)** discipline. It provides endpoints for managing a restaurant's functionality, such as handling employees, orders, tables, and payments.

## Getting Started

Follow these instructions to set up and run the project using **XAMPP** and **Postman**.

### Prerequisites

- **XAMPP** (for Apache and MySQL servers)
- **Postman** (for testing API endpoints)

### How to Run

1. **Start Servers:**
   - Open **XAMPP** and start the **MySQL** and **Apache** servers.

2. **Create Database:**
   - Open **phpMyAdmin** by navigating to `http://localhost/phpmyadmin`.
   - Create a new database called **`restaurante`**.
   - Import the **`init.sql`** file located in the repository to set up the initial schema and data.

3. **Test Endpoints:**
   - Open **Postman**.
   - Use the routes described in the **`rotas.html`** file to test the available endpoints of the webservice.

### Available Endpoints

The endpoints provide CRUD operations for managing various aspects of the restaurant, such as:

- **Employees**: Add, update, delete, and retrieve employee information.
- **Tables**: Manage table status and assignments.
- **Orders**: Create and update orders for customers.
- **Payments**: Handle payment processing for completed orders.

For detailed route information, please refer to the **`rotas.html`** file.

### Directory Structure

```
├── api/               # API endpoints for handling HTTP requests
├── config/            # Configuration files (database connection, utilities)
├── frontend/          # Frontend files (not yet integrated)
├── model/             # Database models for different entities
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
- **Enhanced Documentation**: Add detailed examples for each endpoint in the documentation.

### Authors

- Developed with ❤️ by [@ericksgmes](https://github.com/ericksgmes) and [@leoh3nrique](https://github.com/leoh3nrique)

### License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

### Additional Resources

- **XAMPP**: [Download XAMPP](https://www.apachefriends.org/index.html)
- **Postman**: [Download Postman](https://www.postman.com/downloads/)

Feel free to contribute and open issues to improve this project!
