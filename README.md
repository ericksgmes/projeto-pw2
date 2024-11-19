---

# Restaurante Webservice - PW2 Discipline Project

This repository contains the web application developed for the **Programação para Web 2 (PW2)** discipline. It provides a complete solution for managing a restaurant's functionality, including employee management, orders, tables, and payments, with a fully integrated frontend and backend.

## 📋 Table of Contents

- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [How to Run](#how-to-run)
- [📖 Accessing Swagger Documentation](#-accessing-swagger-documentation)
- [🚀 Available Features](#-available-features)
- [📁 Directory Structure](#-directory-structure)
- [📝 Notes](#-notes)
- [🔮 Future Improvements](#-future-improvements)
- [👥 Authors](#-authors)
- [📄 License](#-license)
- [🔗 Additional Resources](#-additional-resources)

---

## Getting Started

Follow these instructions to set up and run the project using **XAMPP**.

### Prerequisites

- **XAMPP** (for Apache and MySQL servers)
- **Composer** (for managing PHP dependencies)
- **Postman** *(optional, for API testing)*

### How to Run

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/ericksgmes/restaurante-webservice
   ```

2. **Install Dependencies:**

   Navigate to the project directory and run:

   ```bash
   composer install
   ```

3. **Start Servers:**

   - Open **XAMPP**.
   - Start the **Apache** and **MySQL** servers.

4. **Create Database:**

   - Open **phpMyAdmin** by navigating to `http://localhost/phpmyadmin`.
   - Create a new database called **`restaurante`**.
   - Import the **`init.sql`** file located in the repository to set up the initial schema and data.

5. **Access the Application:**

   - Open your web browser.
   - Navigate to:

     ```
     http://localhost/restaurante-webservice/frontend
     ```

   - The frontend application is now integrated with the backend and ready to use.

6. **Test Endpoints (Optional):**

   - Open **Postman** or use the Swagger UI.
   - Use the routes described in the **`swagger.html`** file to test the available endpoints of the webservice.

---

## 📖 Accessing Swagger Documentation

The project includes Swagger UI for easy visualization and testing of the API. To access the Swagger documentation:

1. Ensure that the **Apache** server is running in **XAMPP**.
2. Navigate to:

   ```
   http://localhost/restaurante-webservice/swagger.html
   ```

3. The Swagger interface provides detailed documentation of all available endpoints and allows you to test them directly from the browser.

---

## 🚀 Available Features

The application provides functionalities for managing various aspects of the restaurant, such as:

- **Employees**: Add, update, delete, and retrieve employee information through the frontend interface.
- **Tables**: Manage table status and assignments.
- **Orders**: Create and update orders for customers.
- **Payments**: Handle payment processing for completed orders.

---

## 📁 Directory Structure

```
├── .idea/             # IDE configuration files (optional, for local dev)
├── config/            # Configuration files (database connection, utilities)
├── controller/        # Controllers for handling requests
├── frontend/          # Frontend files (fully integrated with backend)
├── model/             # Database models for different entities
├── .gitignore         # Git ignore rules
├── .htaccess          # Apache configuration file
├── LICENSE            # Project license
├── README.md          # Project documentation
├── composer.json      # PHP dependencies file
├── index.php          # Entry point for the webservice
└── swagger.html       # Swagger UI to view API endpoints
```

---

## 📝 Notes

- **Frontend Integration**: The frontend is now fully integrated with the backend. Access it via `http://localhost/restaurante-webservice/frontend`.
- **API Testing**: You can still use tools like **Postman** or the included Swagger UI to interact with the REST API for testing and development purposes.

---

## 🔮 Future Improvements

- **Authentication**: Implement user authentication to secure API endpoints.

---

## 👥 Authors

Developed with ❤️ by:

- [@ericksgmes](https://github.com/ericksgmes)
- [@leoh3nrique](https://github.com/leoh3nrique)

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🔗 Additional Resources

- **GitHub Repository**: [restaurante-webservice](https://github.com/ericksgmes/restaurante-webservice)
- **XAMPP**: [Download XAMPP](https://www.apachefriends.org/index.html)
- **Composer**: [Download Composer](https://getcomposer.org/download/)
- **Postman** *(optional)*: [Download Postman](https://www.postman.com/downloads/)

---
