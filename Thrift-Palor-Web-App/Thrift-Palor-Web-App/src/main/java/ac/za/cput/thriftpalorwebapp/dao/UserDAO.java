package main.java.ac.za.cput.thriftpalorwebapp.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import ac.za.cput.thriftpalorwebapp.config.DBConfig;
import main.java.ac.za.cput.thriftpalorwebapp.model.User;

public class UserDAO {
    private static final String INSERT_USER = 
        "INSERT INTO \"User\" (username, password_hash, email, first_name, last_name, phone, role) " +
        "VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    private static final String FIND_BY_EMAIL = 
        "SELECT * FROM \"User\" WHERE email = ?";
    
    public User create(User user) throws SQLException {
        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet generatedKeys = null;
        
        try {
            conn = DBConfig.getConnection();
            stmt = conn.prepareStatement(INSERT_USER, Statement.RETURN_GENERATED_KEYS);
            
            stmt.setString(1, user.getUsername());
            stmt.setString(2, user.getPasswordHash());
            stmt.setString(3, user.getEmail());
            stmt.setString(4, user.getFirstName());
            stmt.setString(5, user.getLastName());
            stmt.setString(6, user.getPhone());
            stmt.setString(7, user.getRole().toString());
            
            int affectedRows = stmt.executeUpdate();
            if (affectedRows == 0) {
                throw new SQLException("Creating user failed, no rows affected.");
            }
            
            generatedKeys = stmt.getGeneratedKeys();
            if (generatedKeys.next()) {
                user.setUserId(generatedKeys.getInt(1));
            } else {
                throw new SQLException("Creating user failed, no ID obtained.");
            }
            
            return user;
        } finally {
            if (generatedKeys != null) generatedKeys.close();
            if (stmt != null) stmt.close();
            DBConfig.closeConnection(conn);
        }
    }

    public User findByEmail(String email) throws SQLException {
        Connection conn = null;
        PreparedStatement stmt = null;
        ResultSet rs = null;
        
        try {
            conn = DBConfig.getConnection();
            stmt = conn.prepareStatement(FIND_BY_EMAIL);
            stmt.setString(1, email);
            
            rs = stmt.executeQuery();
            if (rs.next()) {
                return mapRowToUser(rs);
            }
            return null;
        } finally {
            if (rs != null) rs.close();
            if (stmt != null) stmt.close();
            DBConfig.closeConnection(conn);
        }
    }

    private User mapRowToUser(ResultSet rs) throws SQLException {
        User user = new User();
        user.setUserId(rs.getInt("user_id"));
        user.setUsername(rs.getString("username"));
        user.setPasswordHash(rs.getString("password_hash"));
        user.setEmail(rs.getString("email"));
        user.setFirstName(rs.getString("first_name"));
        user.setLastName(rs.getString("last_name"));
        user.setPhone(rs.getString("phone"));
        user.setRole(User.Role.valueOf(rs.getString("role")));
        return user;
    }
}
