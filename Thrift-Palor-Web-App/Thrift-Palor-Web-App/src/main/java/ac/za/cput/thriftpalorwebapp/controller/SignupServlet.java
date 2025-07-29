package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.domain.User;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.json.Json;
import jakarta.json.JsonObject;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/signup")
public class SignupServlet extends HttpServlet {
    private UserDAO userDao;

    @Override
    public void init() throws ServletException {
        userDao = new UserDAO();
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        JsonObject jsonRequest = Json.createReader(req.getReader()).readObject();
        JsonObject jsonResponse;
        resp.setContentType("application/json");

        try {
            User user = new User(
                jsonRequest.getString("username"),
                jsonRequest.getString("password"),
                jsonRequest.getString("email"),
                jsonRequest.getString("firstName"),
                jsonRequest.getString("lastName"),
                jsonRequest.getString("phone")
            );

            User createdUser = userDao.createUser(user);
            jsonResponse = Json.createObjectBuilder()
                .add("success", true)
                .add("userId", createdUser.getUserId())
                .build();
            resp.setStatus(HttpServletResponse.SC_CREATED);
        } catch (SQLException e) {
            jsonResponse = Json.createObjectBuilder()
                .add("success", false)
                .add("message", e.getMessage())
                .build();
            resp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
        }

        resp.getWriter().write(jsonResponse.toString());
    }
}