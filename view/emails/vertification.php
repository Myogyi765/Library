    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0077ff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
            .code-box { background: white; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; border: 2px dashed #0077ff; border-radius: 8px; margin: 20px 0; }
            .btn { display: inline-block; padding: 12px 30px; background: #0077ff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Email Verification</h2>
            </div>
            <div class="content">
                <p>Hello <strong><?php echo htmlspecialchars($name); ?></strong>,</p>
                <p>Thank you for registering with Library Management System. Please verify your email address by clicking the button below or using the verification code.</p>
                
                <div style="text-align: center;">
                    <a href="<?php echo htmlspecialchars($verifyLink); ?>" class="btn">Verify Email Address</a>
                </div>
                
                <p>Or use this verification code:</p>
                <div class="code-box"><?php echo htmlspecialchars($code); ?></div>
                
                <p>This verification link and code will expire in <strong>15 minutes</strong>.</p>
                
                <p>If you did not create an account, please ignore this email.</p>
                
                <hr>
                <div class="footer">
                    <p>Library Management System &copy; <?php echo date('Y'); ?> All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>