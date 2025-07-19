package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.model.User;
import ac.za.cput.thriftpalorwebapp.util.PasswordUtil;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.SQLException;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import org.json.JSONObject;

@WebServlet(name = "SignupServlet", urlPatterns = {"/signup", "/checkUsername", "/checkEmail"})
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
    
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        String path = request.getServletPath();
        response.setContentType("application/json");
        PrintWriter out = response.getWriter();
        JSONObject jsonResponse = new JSONObject();
        
        try {
            if (path.equals("/checkUsername")) {
                String username = request.getParameter("username");
                if (username == null || username.trim().isEmpty()) {
                    response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                    jsonResponse.put("message", "Username parameter is required");
                } else {
                    boolean exists = userDAO.usernameExists(username);
                    jsonResponse.put("exists", exists);
                }
            } else if (path.equals("/checkEmail")) {
                String email = request.getParameter("email");
                if (email == null || email.trim().isEmpty()) {
                    response.setStatus(HttpServletResponse.SC_BAD_REQUEST);
                    jsonResponse.put("message", "Email parameter is required");
                } else {
                    boolean exists = userDAO.emailExists(email);
                    jsonResponse.put("exists", exists);
                }
            }
        } catch (SQLException e) {
            response.setStatus(HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
            jsonResponse.put("message", "Database error");
            e.printStackTrace();
        }
        
        out.print(jsonResponse.toString());
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
            String role = jsonRequest.optString("role", "Buyer");
            
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
            User user = new User(username, passwordHash, email, firstName, lastName, phone, role);
            
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