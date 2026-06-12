<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container-wrapper {
            display: flex;
            background: linear-gradient(135deg, #ffffff 0%, #e8f5e9 50%, #c8e6c9 100%);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-width: 900px;
            width: 90%;
        }

        .branding-section {
            flex: 1;
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            min-height: 500px;
        }

        .logo-wrapper {
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }

        .logo-wrapper:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .logo-wrapper img {
            width: 130px;
            height: 100px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
            transition: filter 0.3s ease;
        }

        .logo-wrapper:hover img {
            filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.3));
        }

        .branding-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .branding-text p {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .content-section {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .header {
            margin-bottom: 40px;
        }

        .header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .header p {
            color: #718096;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus {
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(76, 175, 80, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            border: none;
            color: #2d3748;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .back-link {
            color: #4caf50;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .admin-section {
            display: none;
            margin-top: 30px;
            padding: 20px;
            background: #f0f9ff;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }

        .admin-section.show {
            display: block;
        }

        .request-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
        }

        .request-info p {
            margin: 5px 0;
            color: #2d3748;
            font-size: 0.9rem;
        }

        .request-info strong {
            color: #000;
        }

        .btn-approve {
            background: #48bb78;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-approve:hover {
            background: #38a169;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            text-align: center;
        }

        .modal-content h3 {
            color: #2d3748;
            margin-bottom: 10px;
        }

        .modal-content p {
            color: #718096;
            margin-bottom: 20px;
        }

        .password-display {
            background: #f0f9ff;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            word-break: break-all;
        }

        @media (max-width: 768px) {
            .container-wrapper {
                flex-direction: column;
            }

            .branding-section {
                padding: 40px 30px;
                min-height: auto;
            }

            .branding-text h1 {
                font-size: 2rem;
            }

            .content-section {
                padding: 40px 30px;
            }

            .request-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-approve {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- User Password Reset Form -->
    <div class="container-wrapper" id="userResetContainer">
        <div class="branding-section">
            <div class="logo-wrapper">
                <img src="../dist/img/terelearn.png" alt="TereLearn Logo">
            </div>
            <div class="branding-text">
                <h1>TereLearn</h1>
                <p>A learning management system</p>
            </div>
        </div>

        <div class="content-section">
            <div class="header">
                <h2>Reset Password</h2>
                <p>Request password reset from admin</p>
            </div>

            <div id="alert" class="alert" style="display: none;"></div>

            <form id="resetForm">
                <div class="form-group">
                    <label for="userEmail">Registered Email Address</label>
                    <input
                        type="email"
                        id="userEmail"
                        name="userEmail"
                        class="form-control"
                        placeholder="Enter your registered email"
                        required
                    />
                </div>

                <button type="submit" class="btn-primary mb-3">Request Password Reset</button>

                <a class="back-link" href="signin.php">← Back to Login</a>
            </form>
        </div>
    </div>

   

    <!-- Success Modal -->
    <div class="modal-backdrop" id="successModal">
        <div class="modal-content">
            <h3>✓ Password Reset Sent</h3>
            <p>A new default password has been sent to your registered email address.</p>
            <p style="font-size: 0.85rem; color: #718096;">Your new default password format: FirstInitial + Birthday (MMDDYYYY) + LastInitial</p>
            <button class="btn-primary" onclick="closeModal()">Done</button>
        </div>
    </div>

    <script>
        // Sample database (in real scenario, this would be from backend)
        const registeredUsers = [
            { email: 'renwel@terelearn.com', firstName: 'Renwel', lastName: 'Lucero', birthday: '06151998' },
            { email: 'john@terelearn.com', firstName: 'John', lastName: 'Doe', birthday: '03221995' },
            { email: 'maria@terelearn.com', firstName: 'Maria', lastName: 'Santos', birthday: '12101997' }
        ];

        let resetRequests = [];

        function generateDefaultPassword(firstName, birthday, lastName) {
            const firstInitial = firstName.charAt(0).toUpperCase();
            const lastInitial = lastName.charAt(0).toUpperCase();
            return `${firstInitial}${birthday}${lastInitial}`;
        }

        function findUserByEmail(email) {
            return registeredUsers.find(user => user.email.toLowerCase() === email.toLowerCase());
        }

        function showAlert(message, type = 'error') {
            const alertEl = document.getElementById('alert');
            alertEl.textContent = message;
            alertEl.className = `alert alert-${type}`;
            alertEl.style.display = 'block';
        }

        

        function toggleUserView() {
            document.getElementById('userResetContainer').style.display = document.getElementById('userResetContainer').style.display === 'none' ? 'flex' : 'none';
            document.getElementById('adminContainer').style.display = document.getElementById('adminContainer').style.display === 'none' ? 'flex' : 'none';
        }

        function closeModal() {
            document.getElementById('successModal').classList.remove('show');
            document.getElementById('resetForm').reset();
        }

        document.getElementById('resetForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('userEmail').value;

            const user = findUserByEmail(email);

            if (!user) {
                showAlert('Email not found in our system. Please check and try again.', 'error');
                return;
            }

            // Add to reset requests
            resetRequests.push({
                id: Date.now(),
                email: user.email,
                firstName: user.firstName,
                lastName: user.lastName,
                birthday: user.birthday,
                timestamp: new Date().toLocaleString()
            });

            showAlert('Request submitted successfully! Admin will process your request shortly.', 'success');
            
            // Show admin notification
            setTimeout(() => {
                updateRequestsList();
            }, 500);
        });

        function updateRequestsList() {
            const listEl = document.getElementById('requestsList');
            
            if (resetRequests.length === 0) {
                listEl.innerHTML = '<p style="color: #718096;">No pending requests</p>';
                return;
            }

            listEl.innerHTML = resetRequests.map(req => `
                <div class="request-item">
                    <div class="request-info">
                        <p><strong>Email:</strong> ${req.email}</p>
                        <p><strong>Name:</strong> ${req.firstName} ${req.lastName}</p>
                        <p><strong>Requested:</strong> ${req.timestamp}</p>
                    </div>
                    <button class="btn-approve" onclick="approveReset(${req.id})">Send Default Password</button>
                </div>
            `).join('');
        }

        function approveReset(requestId) {
            const request = resetRequests.find(r => r.id === requestId);
            
            if (!request) return;

            const defaultPassword = generateDefaultPassword(
                request.firstName,
                request.birthday,
                request.lastName
            );

            // Remove from requests
            resetRequests = resetRequests.filter(r => r.id !== requestId);

            // In real scenario, send email here
            console.log(`Sending default password to ${request.email}: ${defaultPassword}`);
            alert(`Default password sent to ${request.email}\n\nDefault Password: ${defaultPassword}`);

            updateRequestsList();
        }


    </script>
</body>
</html>