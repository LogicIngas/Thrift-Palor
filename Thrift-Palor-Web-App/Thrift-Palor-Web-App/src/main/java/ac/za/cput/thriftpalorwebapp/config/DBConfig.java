/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package ac.za.cput.thriftpalorwebapp.config;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.Statement;
/**
 *
 * @author User
 */
public class DBConfig {
    
    private static BasicDataSource dataSource;

    static {
        try {
            // dataSource = new BasicDataSource();
            // dataSource.setUrl("jdbc:derby://localhost:1527/ThriftPalorDB;create=true");
            // dataSource.setUsername("app");
            // dataSource.setPassword("app");
            // dataSource.setMinIdle(5);
            // dataSource.setMaxIdle(10);
            // dataSource.setMaxTotal(25);
            // dataSource.setMaxWaitMillis(10000);
            // dataSource.setValidationQuery("SELECT 1 FROM SYSIBM.SYSDUMMY1");
        } catch (Exception e) {
            throw new RuntimeException("Failed to initialize database connection pool", e);
        }
    }

    public static Connection getConnection() throws SQLException {
        return dataSource.getConnection();
    }

    public static void closeConnection(Connection conn) {
        if (conn != null) {
            try {
                conn.close();
            } catch (SQLException e) {
                System.err.println("Error closing connection: " + e.getMessage());
            }
        }
    }
    
}
