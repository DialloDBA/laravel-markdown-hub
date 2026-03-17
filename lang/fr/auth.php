<?php

return [
    // Laravel core
    'failed'   => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Tentatives de connexion trop nombreuses. Réessayez dans :seconds secondes.',

    // Layout guest
    'panel_title'    => "La documentation\nréinventée.",
    'panel_subtitle' => 'Importez, organisez, fusionnez et exportez vos fichiers Markdown comme un professionnel.',
    'panel_features' => [
        ['icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'label' => 'Dossiers virtuels & organisation intelligente'],
        ['icon' => 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z', 'label' => 'Fusion de fichiers en un clic'],
        ['icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'label' => 'Export PDF professionnel'],
        ['icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'label' => 'Prévisualisation Markdown en temps réel'],
    ],
    'back_home'  => 'Accueil',
    'all_rights' => 'Tous droits réservés.',

    // Pages auth — login
    'login_title'       => 'Bienvenue 👋',
    'login_subtitle'    => 'Connectez-vous à votre espace de travail.',
    'email_label'       => 'Adresse e-mail',
    'email_placeholder' => 'vous@exemple.com',
    'password_label'    => 'Mot de passe',
    'password_placeholder' => '••••••••',
    'forgot_password'   => 'Mot de passe oublié ?',
    'remember_me'       => 'Se souvenir de moi',
    'login_btn'         => 'Se connecter',
    'no_account'        => 'Pas encore de compte ?',
    'register_link'     => 'S\'inscrire gratuitement',

    // Pages auth — register
    'register_title'       => 'Créer un compte',
    'register_subtitle'    => 'Gratuit, sans carte bancaire. Prêt en 30 secondes.',
    'name_label'           => 'Nom complet',
    'name_placeholder'     => 'Jean Dupont',
    'confirm_label'        => 'Confirmer le mot de passe',
    'register_btn'         => 'Créer mon compte',
    'already_registered'   => 'Déjà inscrit ?',
    'login_link'           => 'Se connecter',

    // Pages auth — forgot password
    'forgot_title'    => 'Mot de passe oublié ?',
    'forgot_subtitle' => 'Saisissez votre e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.',
    'forgot_btn'      => 'Envoyer le lien de réinitialisation',
    'back_to_login'   => 'Retour à la connexion',

    // Pages auth — reset password
    'reset_title'    => 'Nouveau mot de passe',
    'reset_subtitle' => 'Choisissez un mot de passe fort pour sécuriser votre compte.',
    'new_password'   => 'Nouveau mot de passe',
    'confirm_new'    => 'Confirmer le mot de passe',
    'reset_btn'      => 'Réinitialiser le mot de passe',
    'pw_placeholder' => 'Minimum 8 caractères',

    // Pages auth — verify email
    'verify_title'    => 'Vérifiez votre e-mail',
    'verify_subtitle' => 'Merci pour votre inscription ! Avant de commencer, veuillez vérifier votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer.',
    'verify_sent'     => 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.',
    'resend_btn'      => 'Renvoyer l\'e-mail de vérification',
    'logout_btn'      => 'Se déconnecter',
];
