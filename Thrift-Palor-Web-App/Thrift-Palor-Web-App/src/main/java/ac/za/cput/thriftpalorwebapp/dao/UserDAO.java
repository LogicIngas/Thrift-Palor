package ac.za.cput.thriftpalorwebapp.dao;

import ac.za.cput.thriftpalorwebapp.connection.DBConnection;
import ac.za.cput.thriftpalorwebapp.domain.User;
import ac.za.cput.thriftpalorwebapp.util.PasswordUtil;
import java.sql.*;
import java.util.logging.Level;
import java.util.logging.Logger;

public class UserDAO {
    private static final Logger LOGGER = Logger.getLogger(UserDAO.class.getName());
    
    private static final String CREATE_TABLE_SQL = "CREATE TABLE Users (" +
            "user_id INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1)," +
            "username VARCHAR(50) NOT NULL UNIQUE," +
            "password_hash VARCHAR(255) NOT NULL," +
            "email VARCHAR(100) NOT NULL UNIQUE," +
            "first_name VARCHAR(50)," +
            "last_name VARCHAR(50)," +
            "phone VARCHAR(20)," +
            "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP," +
            "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP," +
            "PRIMARY KEY (user_id))";

    private static final String INSERT_USER_SQL = "INSERT INTO Users " +
            "(username, password_hash, email, first_name, last_name, phone) " +
            "VALUES (?, ?, ?, ?, ?, ?)";
        
    private static final String FIND_BY_EMAIL_SQL = "SELECT * FROM Users WHERE email = ?";
        
    private static final String FIND_BY_USERNAME_SQL = "SELECT * FROM Users WHERE username = ?";
        
    private static final String CHECK_USERNAME_EXISTS_SQL = "SELECT 1 FROM Users WHERE username = ?";
        
    private static final String CHECK_EMAIL_EXISTS_SQL = "SELECT 1 FROM Users WHERE email = ?";

    public void initializeDatabase() throws SQLException {
        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            if (!tableExists(conn, "USERS")) {
                try (Statement stmt = conn.createStatement()) {
                    stmt.execute(CREATE_TABLE_SQL);
                    LOGGER.info("Users table created successfully");
                    DBConnection.commitConnection(conn);
                }
            }
        } finally {
            DBConnection.closeConnection(conn);
        }
    }

    public User createUser(User user) throws SQLException {
        validateUser(user);
        
        if (usernameExists(user.getUsername())) {
            throw new SQLException("Username already exists");
        }
        
        if (emailExists(user.getEmail())) {
            throw new SQLException("Email already registered");
        }

        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            try (PreparedStatement stmt = conn.prepareStatement(INSERT_USER_SQL, Statement.RETURN_GENERATED_KEYS)) {
                setUserParameters(stmt, user);
                
                int affectedRows = stmt.executeUpdate();
                if (affectedRows == 0) {
                    throw new SQLException("Creating user failed, no rows affected");
                }
                
                setGeneratedUserId(stmt, user);
                DBConnection.commitConnection(conn);
                
                LOGGER.log(Level.INFO, "Created new user: {0}", user.getEmail());
                return user;
            }
        } catch (SQLException e) {
            DBConnection.closeConnection(conn);
            LOGGER.log(Level.SEVERE, "Error creating user", e);
            throw e;
        }
    }

    public User findByEmail(String email) throws SQLException {
        validateEmail(email);
        
        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            try (PreparedStatement stmt = conn.prepareStatement(FIND_BY_EMAIL_SQL)) {
                stmt.setString(1, email);
                
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        return mapResultSetToUser(rs);
                    }
                    return null;
                }
            }
        } finally {
            DBConnection.closeConnection(conn);
        }
    }

    public User findByUsername(String username) throws SQLException {
        validateUsername(username);
        
        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            try (PreparedStatement stmt = conn.prepareStatement(FIND_BY_USERNAME_SQL)) {
                stmt.setString(1, username);
                
                try (ResultSet rs = stmt.executeQuery()) {
                    if (rs.next()) {
                        return mapResultSetToUser(rs);
                    }
                    return null;
                }
            }
        } finally {
            DBConnection.closeConnection(conn);
        }
    }

    public boolean usernameExists(String username) throws SQLException {
        validateUsername(username);
        
        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            try (PreparedStatement stmt = conn.prepareStatement(CHECK_USERNAME_EXISTS_SQL)) {
                stmt.setString(1, username);
                
                try (ResultSet rs = stmt.executeQuery()) {
                    return rs.next();
                }
            }
        } finally {
            DBConnection.closeConnection(conn);
        }
    }

    public boolean emailExists(String email) throws SQLException {
        validateEmail(email);
        
        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            try (PreparedStatement stmt = conn.prepareStatement(CHECK_EMAIL_EXISTS_SQL)) {
                stmt.setString(1, email);
                
                try (ResultSet rs = stmt.executeQuery()) {
                    return rs.next();
                }
            }
        } finally {
            DBConnection.closeConnection(conn);
        }
    }

    // Helper methods
    private boolean tableExists(Connection conn, String tableName) throws SQLException {
        try (ResultSet rs = conn.getMetaData().getTables(null, null, tableName.toUpperCase(), null)) {
            return rs.next();
        }
    }

    private void validateUser(User user) throws SQLException {
        if (user == null) {
            throw new SQLException("User cannot be null");
        }
        validateUsername(user.getUsername());
        validateEmail(user.getEmail());
        
        if (user.getPasswordHash() == null || user.getPasswordHash().trim().isEmpty()) {
            throw new SQLException("Password cannot be empty");
        }
    }

    private void validateUsername(String username) throws SQLException {
        if (username == null || username.trim().isEmpty()) {
            throw new SQLException("Username cannot be empty");
        }
        if (username.length() < 3 || username.length() > 50) {
            throw new SQLException("Username must be between 3 and 50 characters");
        }
    }

    private void validateEmail(String email) throws SQLException {
        if (email == null || email.trim().isEmpty()) {
            throw new SQLException("Email cannot be empty");
        }
        if (!email.contains("@") || email.length() > 100) {
            throw new SQLException("Invalid email address");
        }
    }

    private void setUserParameters(PreparedStatement stmt, User user) throws SQLException {
        stmt.setString(1, user.getUsername());
        stmt.setString(2, PasswordUtil.hashPassword(user.getPasswordHash()));
        stmt.setString(3, user.getEmail());
        stmt.setString(4, user.getFirstName());
        stmt.setString(5, user.getLastName());
        stmt.setString(6, user.getPhone());
    }

    private void setGeneratedUserId(PreparedStatement stmt, User user) throws SQLException {
        try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
            if (generatedKeys.next()) {
                user.setUserId(generatedKeys.getInt(1));
            } else {
                throw new SQLException("Creating user failed, no ID obtained");
            }
        }
    }

    private User mapResultSetToUser(ResultSet rs) throws SQLException {
        User user = new User();
        user.setUserId(rs.getInt("user_id"));
        user.setUsername(rs.getString("username"));
        user.setPasswordHash(rs.getString("password_hash"));
        user.setEmail(rs.getString("email"));
        user.setFirstName(rs.getString("first_name"));
        user.setLastName(rs.getString("last_name"));
        user.setPhone(rs.getString("phone"));
        user.setCreatedAt(rs.getTimestamp("created_at"));
        user.setUpdatedAt(rs.getTimestamp("updated_at"));
        return user;
    }
}