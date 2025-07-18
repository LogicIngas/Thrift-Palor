package ac.za.cput.thriftpalorwebapp.dao;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DBConnection {
    
    private static final String URL = "jdbc:derby://localhost:1527/thriftdb;create=true";
    private static final String USER = "app";
    private static final String PASS = "app";

    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASS);
    }
}
