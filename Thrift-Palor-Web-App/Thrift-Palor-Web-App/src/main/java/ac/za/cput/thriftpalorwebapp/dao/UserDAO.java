package ac.za.cput.thriftpalorwebapp.model;

import ac.za.cput.thriftpalorwebapp.model.User;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class UserDAO {

    private static final String CREATE_TABLE_SQL =
    "CREATE TABLE Users (" +
    "user_id INT NOT NULL AUTO_INCREMENT," +
    "username VARCHAR(50) NOT NULL UNIQUE," +
    "password_hash VARCHAR(255) NOT NULL," +
    "email VARCHAR(100) NOT NULL UNIQUE," +
    "first_name VARCHAR(50)," +
    "last_name VARCHAR(50)," +
    "phone VARCHAR(20)," +
    "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP," +
    "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP," +
    "PRIMARY KEY (user_id)" +
    ")";

    public void createTable() throws SQLException {
        Connection conn = null;
        Statement stmt = null;
        
        try {
            conn = DBConnection.getConnection();
            if (!tableExists(conn, "USERS")) {
                stmt = conn.createStatement();
                stmt.execute(CREATE_TABLE_SQL);
                System.out.println("Users table created successfully");
            }
        } finally {
            if (stmt != null) stmt.close();
            DBConnection.closeConnection(conn);
        }
    }
    
    private boolean tableExists(Connection conn, String tableName) throws SQLException {
        ResultSet rs = conn.getMetaData().getTables(null, null, tableName.toUpperCase(), null);
        boolean exists = rs.next();
        rs.close();
        return exists;
    }
    
    public boolean insertUser(User user) throws SQLException {
        String sql = "INSERT INTO Users (username, password_hash, email, first_name, last_name, phone) "
                   + "VALUES (?, ?, ?, ?, ?, ?)";
        
        Connection conn = null;
        PreparedStatement stmt = null;
        
        try {
            conn = DBConnection.getConnection();
            stmt = conn.prepareStatement(sql);
            
            stmt.setString(1, user.getUsername());
            stmt.setString(2, user.getPasswordHash());
            stmt.setString(3, user.getEmail());
            stmt.setString(4, user.getFirstName());
            stmt.setString(5, user.getLastName());
            stmt.setString(6, user.getPhone());

            int rows = stmt.executeUpdate();
            return rows > 0;
        } finally {
            if (stmt != null) stmt.close();
            DBConnection.closeConnection(conn);
        }
    }
    
    public boolean usernameExists(String username) throws SQLException {
        String sql = "SELECT 1 FROM Users WHERE username = ?";
        
        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet rs = null;
        
        try {
            conn = DBConnection.getConnection();
            stmt = conn.prepareStatement(sql);
            stmt.setString(1, username);
            rs = stmt.executeQuery();
            return rs.next();
        } finally {
            if (rs != null) rs.close();
            if (stmt != null) stmt.close();
            DBConnection.closeConnection(conn);
        }
    }
    
    public boolean emailExists(String email) throws SQLException {
        String sql = "SELECT 1 FROM Users WHERE email = ?";
        
        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet rs = null;
        
        try {
            conn = DBConnection.getConnection();
            stmt = conn.prepareStatement(sql);
            stmt.setString(1, email);
            rs = stmt.executeQuery();
            return rs.next();
        } finally {
            if (rs != null) rs.close();
            if (stmt != null) stmt.close();
            DBConnection.closeConnection(conn);
        }
    }
}