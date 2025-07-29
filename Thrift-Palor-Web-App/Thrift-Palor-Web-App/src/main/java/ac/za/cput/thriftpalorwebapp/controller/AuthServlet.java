package ac.za.cput.thriftpalorwebapp.controller;

import ac.za.cput.thriftpalorwebapp.dao.UserDAO;
import ac.za.cput.thriftpalorwebapp.domain.User;
import ac.za.cput.thriftpalorwebapp.util.SecurityUtil;
import javax.servlet.*;
import javax.servlet.http.*;
import javax.servlet.annotation.*;
import java.io.IOException;
import java.sql.SQLException;

@WebServlet("/auth/*")
public class AuthServlet extends HttpServlet {
    private UserDAO userDao;

    @Override
    public void init() throws ServletException {
        super.init();
        userDao = new UserDAO();
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        
        String path = request.getPathInfo();
        
        try {
            if (path.equals("/signup")) {
                handleSignup(request, response);
            } else if (path.equals("/login")) {
                handleLogin(request, response);
            } else {
                response.sendError(HttpServletResponse.SC_NOT_FOUND);
            }
        } catch (SQLException e) {
            handleError(response, "Database error: " + e.getMessage(), 
                HttpServletResponse.SC_INTERNAL_SERVER_ERROR);
        }
    }

    private void handleSignup(HttpServletRequest request, HttpServletResponse response)
            throws SQLException, IOException {
        
        User user = new User();
        user.setUsername(request.getParameter("username"));
        user.setEmail(request.getParameter("email"));
        user.setPasswordHash(SecurityUtil.hashPassword(request.getParameter("password")));
        user.setFirstName(request.getParameter("firstName"));
        user.setLastName(request.getParameter("lastName"));
        user.setPhone(request.getParameter("phone"));
        user.setRole(User.Role.valueOf(request.getParameter("role")));
        
        user = userDao.create(user);
        
        HttpSession session = request.getSession();
        session.setAttribute("user", user);
        
        response.sendRedirect(request.getContextPath() + "/front-end/home.html");
    }

    private void handleLogin(HttpServletRequest request, HttpServletResponse response)
            throws SQLException, IOException {
        
        String email = request.getParameter("email");
        String password = request.getParameter("password");
        
        User user = userDao.findByEmail(email);
        
        if (user != null && SecurityUtil.checkPassword(password, user.getPasswordHash())) {
            HttpSession session = request.getSession();
            session.setAttribute("user", user);
            response.sendRedirect(request.getContextPath() + "/front-end/home.html");
        } else {
            response.sendRedirect(request.getContextPath() + 
                "/front-end/login.html?error=Invalid+credentials");
        }
    }

    private void handleError(HttpServletResponse response, String message, int statusCode)
            throws IOException {
        response.setStatus(statusCode);
        response.setContentType("application/json");
        response.getWriter().write(
            String.format("{\"error\": \"%s\"}", message)
        );
    }
}