package ac.za.cput.thriftpalorwebapp.connection;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

public class DBConnection {
    private static final Logger LOGGER = Logger.getLogger(DBConnection.class.getName());
    private static final String DB_URL = "jdbc:derby://localhost:1527/Thriftdb;create=true";
    private static final String USERNAME = "administrator";
    private static final String PASSWORD = "admin";

    public static Connection getConnection() throws SQLException {
        try {
            Connection conn = DriverManager.getConnection(DB_URL, USERNAME, PASSWORD);
            conn.setAutoCommit(false); // Enable transaction management
            return conn;
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Failed to create database connection", e);
            throw e;
        }
    }

    public static void closeConnection(Connection conn) {
        if (conn != null) {
            try {
                if (!conn.isClosed()) {
                    conn.rollback(); // Rollback any pending transactions
                    conn.close();
                }
            } catch (SQLException e) {
                LOGGER.log(Level.WARNING, "Error closing connection", e);
            }
        }
    }

    public static void commitConnection(Connection conn) throws SQLException {
        if (conn != null && !conn.isClosed()) {
            conn.commit();
        }
    }
}