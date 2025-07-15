/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package ac.za.cput.thriftpalorwebapp;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;

/**
 *
 * @author User
 */
public class ThriftPalorWebApp {

    public static void main(String[] args) {
        System.out.println("Hello World!");
        
        // Connect to the embedded Derby database (will create if it doesn't exist)
        final String URL = "jdbc:derby:MyDatabase;create=true";
        final String USERNAME = "";
        final String PASSWORD = "";
        
               

        try (Connection conn = DriverManager.getConnection(URL)) {
            Statement stmt = conn.createStatement();

            String createTableSQL = "CREATE TABLE users (" +
                    "user_id INT PRIMARY KEY GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1), " +
                    "username VARCHAR(100) UNIQUE NOT NULL, " +
                    "password VARCHAR(255) NOT NULL, " +
                    "email VARCHAR(255) UNIQUE NOT NULL, " +
                    "first_name VARCHAR(100), " +
                    "last_name VARCHAR(100), " +
                    "phone VARCHAR(20), " +
                    "role VARCHAR(50), " +
                    "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, " +
                    "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" +
                    ")";

            stmt.executeUpdate(createTableSQL);
            System.out.println("Table 'users' created successfully.");

        } catch (SQLException e) {
            if (e.getSQLState().equals("X0Y32")) {
                System.out.println("Table already exists.");
            } else {
                e.printStackTrace();
            }
        }
    }
}
