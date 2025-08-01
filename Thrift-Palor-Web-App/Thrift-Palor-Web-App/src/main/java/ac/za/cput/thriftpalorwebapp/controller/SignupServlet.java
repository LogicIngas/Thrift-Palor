package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.domain.User;
import com.google.gson.Gson;
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
        resp.setHeader("Access-Control-Allow-Origin", "*");
        resp.setHeader("Access-Control-Allow-Methods", "POST");
        resp.setHeader("Access-Control-Allow-Headers", "Content-Type");

        try (BufferedReader reader = req.getReader()) {
            // Automatically parse JSON into User object using Gson
            User user = new Gson().fromJson(reader, User.class);

            // Create user in DB
            User createdUser = userDao.createUser(user);

            // Respond with success
            resp.getWriter().print(String.format(
                "{\"success\":true,\"message\":\"Account created\",\"userId\":%d}",
                createdUser.getUserId()
            ));
        } catch (Exception e) {
            resp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
            resp.getWriter().print(String.format(
                "{\"success\":false,\"message\":\"%s\"}",
                e.getMessage()
            ));
        }
    }
}
