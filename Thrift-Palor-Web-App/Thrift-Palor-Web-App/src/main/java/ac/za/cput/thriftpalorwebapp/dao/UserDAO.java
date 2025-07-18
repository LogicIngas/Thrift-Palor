package main.java.ac.za.cput.thriftpalorwebapp.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import ac.za.cput.thriftpalorwebapp.config.DBConfig;
import main.java.ac.za.cput.thriftpalorwebapp.model.User;

public class UserDAO {

    // Method to create a table in the database
     
    String create_table_sql = "CREATE TABLE Users (\r\n" + //
                "    user_id INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),\r\n" + //
                "    username VARCHAR(50) NOT NULL,\r\n" + //
                "    password_hash VARCHAR(255) NOT NULL,\r\n" + //
                "    email VARCHAR(100) NOT NULL,\r\n" + //
                "    first_name VARCHAR(50),\r\n" + //
                "    last_name VARCHAR(50),\r\n" + //
                "    phone VARCHAR(20),\r\n" + //
                "    role VARCHAR(10) CHECK (role IN ('Buyer', 'Seller', 'Admin')),\r\n" + //
                "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n" + //
                "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\r\n" + //
                "    PRIMARY KEY (user_id)\r\n" + //
                ");";

    public boolean insertUser(String username, String passwordHash, String email, 
                              String firstName, String lastName, String phone, String role) {
        String sql = "INSERT INTO Users (username, password_hash, email, first_name, last_name, phone, role) " +
                     "VALUES (?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
             
            stmt.setString(1, username);
            stmt.setString(2, passwordHash); // Consider using SHA-256 or bcrypt
            stmt.setString(3, email);
            stmt.setString(4, firstName);
            stmt.setString(5, lastName);
            stmt.setString(6, phone);
            stmt.setString(7, role);

            int rows = stmt.executeUpdate();
            return rows > 0;

        } catch (Exception e) {
            e.printStackTrace();
            return false;
        }
    }
}
