<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="background-container"></div>
    
    <div class="overlay">
        <form action="process_signup.php" method="POST" class="signup-form">
            <h2>Create Account</h2>
            <p class="subtitle">Join us and start your journey</p>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" name="first_name" id="firstName" placeholder="Enter your first name" required>
                </div>
                
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="last_name" required placeholder="Enter your last name">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email address">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a password" minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required placeholder="Confirm your password">
                </div>
            </div>
            
            <div class="form-group">
                <label for="dateOfBirth">Date of Birth</label>
                <input type="date" id="dateOfBirth" name="dob" required>
            </div>
            
            
            <button type="submit" class="signup-btn">Create Account</button>
            
            <div class="form-links">
                <span>Already have an account?</span>
                <a href="signin.php">Sign In</a>
            </div>
        </form>
    </div>

    <script>
        function handleSignUp(event) {
            
            // Password validation
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters long!');
                return;
            }
            
            if (!terms) {
                alert('Please accept the Terms & Conditions to continue.');
                return;
            }
            
            // Simulate sign up process
            const button = document.querySelector('.signup-btn');
            const originalText = button.textContent;
            button.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
            button.textContent = 'Creating Account...';
            button.disabled = true;
            
            setTimeout(() => {
                button.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                button.textContent = originalText;
                button.disabled = false;
                
                // Reset form
                document.querySelector('.signup-form').reset();
            }, 2000);
        }

        // Add subtle parallax effect to background
        document.addEventListener('mousemove', (e) => {
            const background = document.querySelector('.background-container');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            background.style.transform = `translate(${x * 8}px, ${y * 8}px) scale(1.01)`;
        });

        // Real-time password match validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '#e1e1e1';
            }
        });
    </script>
</body>
</html>
