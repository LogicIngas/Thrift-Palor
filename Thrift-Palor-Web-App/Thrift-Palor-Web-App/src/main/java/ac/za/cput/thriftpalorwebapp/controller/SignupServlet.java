package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.model.User;
import ac.za.cput.thriftpalorwebapp.util.PasswordUtil;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.SQLException;
import org.json.JSONObject;

@WebServlet(name = "SignupServlet", urlPatterns = {"/signup"})
public class SignupServlet extends HttpServlet {
    private UserDAO userDAO;
    
    @Override
    public void init() throws ServletException {
        super.init();
        userDAO = new UserDAO();
        try {
            userDAO.createTable();
        } catch (SQLException e) {
            throw new ServletException("Failed to initialize database", e);
        }
    }
    
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
            String firstName = jsonRequest.getString("firstName");
            String lastName = jsonRequest.getString("lastName");
            String phone = jsonRequest.getString("phone");
            
            // Validate input
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
            
            // Hash the password
            String passwordHash = PasswordUtil.hashPassword(password);
            
            // Create user object
            User user = new User(username, passwordHash, email, firstName, lastName, phone);
            
            // Insert into database
            boolean success = userDAO.insertUser(user);
            
            if (success) {
                jsonResponse.put("success", true);
                jsonResponse.put("message", "User registered successfully");
                response.setStatus(HttpServletResponse.SC_CREATED);
            } else {
                jsonResponse.put("success", false);
                jsonResponse.put("message", "Failed to register user");
                response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            }
            
        } catch (Exception e) {
            e.printStackTrace();
            jsonResponse.put("success", false);
            jsonResponse.put("message", "Server error: " + e.getMessage());
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
        
        out.print(jsonResponse.toString());
    }
}