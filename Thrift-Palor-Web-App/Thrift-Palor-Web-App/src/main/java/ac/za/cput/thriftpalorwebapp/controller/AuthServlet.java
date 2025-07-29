package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.domain.User;
import ac.za.cput.thriftpalorwebapp.util.PasswordUtil;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import java.io.IOException;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

@WebServlet("/auth/*")
public class AuthServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(AuthServlet.class.getName());
    private UserDAO userDao;

    @Override
    public void init() throws ServletException {
        super.init();
        userDao = new UserDAO();
        try {
            userDao.initializeDatabase();
            LOGGER.info("Database initialization completed successfully");
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Database initialization failed", e);
            throw new ServletException("Failed to initialize database", e);
        }
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        String path = request.getPathInfo();
        if (path == null) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing path information");
            return;
        }
        
        try {
            if (path.equals("/signup")) {
                handleSignup(request, response);
            } else if (path.equals("/login")) {
                handleLogin(request, response);
            } else {
                response.sendError(HttpServletResponse.SC_NOT_FOUND, "Invalid endpoint");
            }
        } catch (SQLException e) {
            handleError(request, response, "Database error: " + e.getMessage());
        }
    }

    private void handleSignup(HttpServletRequest request, HttpServletResponse response)
            throws SQLException, IOException, ServletException {
        
        // Get all parameters from request
        String username = request.getParameter("username");
        String password = request.getParameter("password");
        String email = request.getParameter("email");
        String firstName = request.getParameter("firstName");
        String lastName = request.getParameter("lastName");
        String phone = request.getParameter("phone");

        // Create user object
        User user = new User();
        user.setUsername(username);
        user.setPasswordHash(password); // Will be hashed in DAO
        user.setEmail(email);
        user.setFirstName(firstName);
        user.setLastName(lastName);
        user.setPhone(phone);

        try {
            // Attempt to create user
            User createdUser = userDao.createUser(user);
            
            // Set user in session
            HttpSession session = request.getSession();
            session.setAttribute("user", createdUser);
            
            // Log success
            LOGGER.log(Level.INFO, "User registered successfully: {0}", email);
            
            // Redirect to home page
            response.sendRedirect(request.getContextPath() + "/home.jsp");
        } catch (SQLException e) {
            // Log error
            LOGGER.log(Level.WARNING, "Signup failed for email: {0}", email);
            
            // Set error message and forward back to signup page
            request.setAttribute("error", e.getMessage());
            request.getRequestDispatcher("/signup.jsp").forward(request, response);
        }
    }

    private void handleLogin(HttpServletRequest request, HttpServletResponse response)
            throws SQLException, IOException, ServletException {
        
        String email = request.getParameter("email");
        String password = request.getParameter("password");
        
        try {
            User user = userDao.findByEmail(email);
            
            if (user != null && PasswordUtil.checkPassword(password, user.getPasswordHash())) {
                // Successful login
                HttpSession session = request.getSession();
                session.setAttribute("user", user);
                
                LOGGER.log(Level.INFO, "User logged in: {0}", email);
                response.sendRedirect(request.getContextPath() + "/home.jsp");
            } else {
                // Failed login
                LOGGER.log(Level.WARNING, "Failed login attempt for email: {0}", email);
                request.setAttribute("error", "Invalid email or password");
                request.getRequestDispatcher("/login.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Login error for email: " + email, e);
            handleError(request, response, "Login processing failed");
        }
    }

    private void handleError(HttpServletRequest request, HttpServletResponse response, String errorMessage)
            throws ServletException, IOException {
        LOGGER.log(Level.SEVERE, errorMessage);
        request.setAttribute("error", errorMessage);
        request.getRequestDispatcher("/error.jsp").forward(request, response);
    }

    @Override
    public void destroy() {
        // Clean up resources if needed
        userDao = null;
        super.destroy();
    }
}