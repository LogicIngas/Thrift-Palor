package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.domain.User;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.io.PrintWriter;
import java.sql.SQLException;

@WebServlet("/signup")
public class SignupServlet extends HttpServlet {
    private UserDAO userDao;

    @Override
    public void init() throws ServletException {
        userDao = new UserDAO();
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) 
            throws ServletException, IOException {
        
        resp.setContentType("application/json");
        PrintWriter out = resp.getWriter();
        
        try {
            String username = req.getParameter("username");
            String password = req.getParameter("password");
            String email = req.getParameter("email");
            String firstName = req.getParameter("firstName");
            String lastName = req.getParameter("lastName");
            String phone = req.getParameter("phone");

            User user = new User(username, password, email, firstName, lastName, phone);
            User createdUser = userDao.createUser(user);
            
            out.print(String.format(
                "{\"success\": true, \"userId\": %d}", 
                createdUser.getUserId()
            ));
            resp.setStatus(HttpServletResponse.SC_CREATED);
            
        } catch (SQLException e) {
            out.print(String.format(
                "{\"success\": false, \"message\": \"%s\"}", 
                e.getMessage()
            ));
            resp.setStatus(HttpServletResponse.SC_BAD_REQUEST);
        }
    }
}