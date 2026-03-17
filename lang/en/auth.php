<?php

return [
    // Laravel core
    'failed'   => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Layout guest
    'panel_title'    => "Documentation\nreinvented.",
    'panel_subtitle' => 'Import, organize, merge and export your Markdown files like a professional.',
    'panel_features' => [
        ['icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'label' => 'Virtual folders & smart organization'],
        ['icon' => 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z', 'label' => 'One-click file merging'],
        ['icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'label' => 'Professional PDF export'],
        ['icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'label' => 'Real-time Markdown preview'],
    ],
    'back_home'  => 'Home',
    'all_rights' => 'All rights reserved.',

    // Pages auth — login
    'login_title'          => 'Welcome back 👋',
    'login_subtitle'       => 'Sign in to your workspace.',
    'email_label'          => 'Email address',
    'email_placeholder'    => 'you@example.com',
    'password_label'       => 'Password',
    'password_placeholder' => '••••••••',
    'forgot_password'      => 'Forgot password?',
    'remember_me'          => 'Remember me',
    'login_btn'            => 'Sign in',
    'no_account'           => "Don't have an account?",
    'register_link'        => 'Sign up free',

    // Pages auth — register
    'register_title'     => 'Create your account',
    'register_subtitle'  => 'Free, no credit card. Ready in 30 seconds.',
    'name_label'         => 'Full name',
    'name_placeholder'   => 'John Doe',
    'confirm_label'      => 'Confirm password',
    'register_btn'       => 'Create my account',
    'already_registered' => 'Already have an account?',
    'login_link'         => 'Sign in',

    // Pages auth — forgot password
    'forgot_title'    => 'Forgot your password?',
    'forgot_subtitle' => "Enter your email and we'll send you a link to reset your password.",
    'forgot_btn'      => 'Send reset link',
    'back_to_login'   => 'Back to sign in',

    // Pages auth — reset password
    'reset_title'    => 'Set new password',
    'reset_subtitle' => 'Choose a strong password to secure your account.',
    'new_password'   => 'New password',
    'confirm_new'    => 'Confirm new password',
    'reset_btn'      => 'Reset password',
    'pw_placeholder' => 'Minimum 8 characters',

    // Pages auth — verify email
    'verify_title'    => 'Verify your email',
    'verify_subtitle' => "Thanks for signing up! Please verify your email address by clicking the link we just sent you.",
    'verify_sent'     => 'A new verification link has been sent to your email address.',
    'resend_btn'      => 'Resend verification email',
    'logout_btn'      => 'Log out',
];
