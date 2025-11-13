<!DOCTYPE html>
<html>
<head>
    <title>Sign In</title>
    <link rel="stylesheet" href="signin.css">
</head>

<body>
    <div class="background-container"></div>
    
    <div class="overlay">
        <form class="signin-form" action="process_signin.php" method="POST">
            <h2>Welcome Back</h2>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <!-- <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div> -->
            
            <button type="submit" class="signin-btn">Sign In</button>
            
            <div class="form-links">
                Don't have an account? <a href="signup.php" onclick="signup.php">Create Account</a>
            </div>
        </form>
    </div>

    <script>
        function handleSignIn(event) {
            // Simulate sign in process
            const button = document.querySelector('.signin-btn');
            button.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
            button.textContent = 'Signing In...';
            
            setTimeout(() => {
                button.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                button.textContent = 'Sign In';
            }, 1500);
        }

        // Add subtle parallax effect to background
        document.addEventListener('mousemove', (e) => {
            const background = document.querySelector('.background-container');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            background.style.transform = `translate(${x * 10}px, ${y * 10}px) scale(1.02)`;
        });
    </script>
</body>
</html>
