package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.domain.User;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.BufferedReader;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/signup")
public class SignupServlet extends HttpServlet {
    private UserDAO userDao;

    @Override
    public void init() throws ServletException {
        userDao = new UserDAO();
        try {
            userDao.initializeDatabase();
        } catch (SQLException e) {
            throw new ServletException("Failed to initialize database", e);
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        resp.setContentType("application/json");
        resp.setCharacterEncoding("UTF-8");
        
        try {
            StringBuilder sb = new StringBuilder();
            BufferedReader reader = req.getReader();
            String line;
            while ((line = reader.readLine()) != null) {
                sb.append(line);
            }
            
            String json = sb.toString();
            json = json.replaceAll("[{}\"]", "");
            String[] keyValues = json.split(",");
            
            User user = new User();
            for (String keyValue : keyValues) {
                String[] parts = keyValue.split(":");
                String key = parts[0].trim();
                String value = parts[1].trim();
                
                switch (key) {
                    case "username": user.setUsername(value); break;
                    case "password": user.setPasswordHash(value); break;
                    case "email": user.setEmail(value); break;
                    case "firstName": user.setFirstName(value); break;
                    case "lastName": user.setLastName(value); break;
                    case "phone": user.setPhone(value); break;
                }
            }
            
            User createdUser = userDao.createUser(user);
            
            resp.getWriter().print(String.format("{\"success\": true, \"message\": \"Account created successfully\", \"userId\": %d}", createdUser.getUserId()));
            resp.setStatus(HttpServletResponse.SC_CREATED);
            
        } catch (Exception e) {
            resp.getWriter().print(String.format("{\"success\": false, \"message\": \"%s\"}", e.getMessage()));
            resp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
        }
    }
}