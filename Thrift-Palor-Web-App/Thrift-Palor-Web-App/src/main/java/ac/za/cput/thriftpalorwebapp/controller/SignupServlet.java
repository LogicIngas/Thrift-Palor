// SignupServlet.java
package main.java.ac.za.cput.thriftpalorwebapp.controller;

import javax.servlet.*;
import javax.servlet.http.*;
import java.io.*;

public class SignupServlet extends HttpServlet {
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String username = request.getParameter("username");
        String password = request.getParameter("password"); // Hash this in production
        String email = request.getParameter("email");
        String firstName = request.getParameter("firstName");
        String lastName = request.getParameter("lastName");
        String phone = request.getParameter("phone");
        String role = "Buyer"; // Default role

        UserDAO dao = new UserDAO();
        boolean success = dao.insertUser(username, password, email, firstName, lastName, phone, role);

        if (success) {
            response.sendRedirect("login.html");
        } else {
            response.getWriter().println("Signup failed. Try again.");
        }
    }
}
