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

    private static final String URL = "jdbc:derby://localhost:1527/thriftdb;create=true";
    private static final String USER = "app";
    private static final String PASS = "app";

    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASS);
    }
    
    public static void main(String[] args) {
        System.out.println("Hello World!");
       
    }
}
