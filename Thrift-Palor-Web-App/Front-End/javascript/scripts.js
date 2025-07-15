 document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('thriftpalor_user')) {
                document.getElementById('auth-wall').style.display = 'none';
                document.getElementById('app-content').style.display = 'block';
            }
        });