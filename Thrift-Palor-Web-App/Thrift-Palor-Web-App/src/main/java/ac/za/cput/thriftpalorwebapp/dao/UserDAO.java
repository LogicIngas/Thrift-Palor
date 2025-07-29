package ac.za.cput.thriftpalorwebapp.dao;

import ac.za.cput.thriftpalorwebapp.connection.DBConnection;
import ac.za.cput.thriftpalorwebapp.domain.User;
import ac.za.cput.thriftpalorwebapp.util.PasswordUtil;
import java.sql.*;
import java.util.logging.Level;
import java.util.logging.Logger;

public class UserDAO {
    private static final Logger LOGGER = Logger.getLogger(UserDAO.class.getName());
    private static final String CREATE_TABLE_SQL = "CREATE TABLE Users (user_id INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1), username VARCHAR(50) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, email VARCHAR(100) NOT NULL UNIQUE, first_name VARCHAR(50), last_name VARCHAR(50), phone VARCHAR(20), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (user_id))";
    private static final String INSERT_USER_SQL = "INSERT INTO Users (username, password_hash, email, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?, ?)";

    public void initializeDatabase() throws SQLException {
        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            if (!tableExists(conn, "USERS")) {
                try (Statement stmt = conn.createStatement()) {
                    stmt.execute(CREATE_TABLE_SQL);
                    DBConnection.commitConnection(conn);
                }
            }
        } finally {
            DBConnection.closeConnection(conn);
        }
    }

    public User createUser(User user) throws SQLException {
        if (user.getUsername() == null || user.getPasswordHash() == null || user.getEmail() == null || user.getFirstName() == null) {
            throw new SQLException("Missing required fields");
        }
        
        Connection conn = null;
        try {
            conn = DBConnection.getConnection();
            try (PreparedStatement stmt = conn.prepareStatement(INSERT_USER_SQL, Statement.RETURN_GENERATED_KEYS)) {
                stmt.setString(1, user.getUsername());
                stmt.setString(2, PasswordUtil.hashPassword(user.getPasswordHash()));
                stmt.setString(3, user.getEmail());
                stmt.setString(4, user.getFirstName());
                stmt.setString(5, user.getLastName());
                stmt.setString(6, user.getPhone());

                int affectedRows = stmt.executeUpdate();
                if (affectedRows == 0) {
                    throw new SQLException("Creating user failed, no rows affected");
                }

                try (ResultSet generatedKeys = stmt.getGeneratedKeys()) {
                    if (generatedKeys.next()) {
                        user.setUserId(generatedKeys.getInt(1));
                    }
                }

                DBConnection.commitConnection(conn);
                return user;
            }
        } catch (SQLException e) {
            if (conn != null) {
                conn.rollback();
            }
            throw e;
        } finally {
            DBConnection.closeConnection(conn);
        }
    }

    private boolean tableExists(Connection conn, String tableName) throws SQLException {
        try (ResultSet rs = conn.getMetaData().getTables(null, null, tableName.toUpperCase(), null)) {
            return rs.next();
        }
    }
}