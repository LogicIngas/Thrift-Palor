package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.domain.User;
import ac.za.cput.thriftpalorwebapp.util.PasswordUtil;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;
import org.json.JSONObject;

@WebServlet(name = "SignupServlet", urlPatterns = {"/signup"})
public class SignupServlet extends HttpServlet {
    private static final Logger LOGGER = Logger.getLogger(SignupServlet.class.getName());
    private UserDAO userDAO;
    
    @Override
    public void init() throws ServletException {
        super.init();
        userDAO = new UserDAO();
        try {
            userDAO.initializeDatabase();
            LOGGER.info("Database initialized successfully");
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Database initialization failed", e);
            throw new ServletException("Failed to initialize database", e);
        }
    }
    
    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        response.setContentType("application/json");
        PrintWriter out = response.getWriter();
        JSONObject jsonResponse = new JSONObject();
        
        try {
            // Read JSON data from request
            StringBuilder sb = new StringBuilder();
            String line;
            while ((line = request.getReader().readLine()) != null) {
                sb.append(line);
            }
            JSONObject jsonRequest = new JSONObject(sb.toString());
            
            // Extract user data
            String username = jsonRequest.getString("username");
            String password = jsonRequest.getString("password");
            String email = jsonRequest.getString("email");
            String firstName = jsonRequest.optString("firstName", "");
            String lastName = jsonRequest.optString("lastName", "");
            String phone = jsonRequest.optString("phone", "");
            
            // Check for existing username/email
            if (userDAO.usernameExists(username)) {
                jsonResponse.put("success", false);
                jsonResponse.put("message", "Username already exists");
                response.setStatus(HttpServletResponse.SC_CONFLICT);
                out.print(jsonResponse.toString());
                return;
            }
            
            if (userDAO.emailExists(email)) {
                jsonResponse.put("success", false);
                jsonResponse.put("message", "Email already registered");
                response.setStatus(HttpServletResponse.SC_CONFLICT);
                out.print(jsonResponse.toString());
                return;
            }
            
            // Create and save user
            User user = new User();
            user.setUsername(username);
            user.setPasswordHash(password); // Will be hashed in DAO
            user.setEmail(email);
            user.setFirstName(firstName);
            user.setLastName(lastName);
            user.setPhone(phone);
            
            User createdUser = userDAO.createUser(user);
            
            // Prepare success response
            jsonResponse.put("success", true);
            jsonResponse.put("message", "User registered successfully");
            jsonResponse.put("userId", createdUser.getUserId());
            response.setStatus(HttpServletResponse.SC_CREATED);
            
            LOGGER.log(Level.INFO, "New user registered: {0}", email);
            
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Registration error", e);
            jsonResponse.put("success", false);
            jsonResponse.put("message", e.getMessage());
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        } catch (Exception e) {
            LOGGER.log(Level.SEVERE, "Unexpected error", e);
            jsonResponse.put("success", false);
            jsonResponse.put("message", "An unexpected error occurred");
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        } finally {
            out.print(jsonResponse.toString());
            out.close();
        }
    }
    
    @Override
    public void destroy() {
        userDAO = null;
        super.destroy();
    }
}