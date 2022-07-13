<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookshelf - Recuperação de Senha</title>
    <link rel="stylesheet" href="style/recover_password.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Gentium+Book+Basic:ital,wght@0,400;0,700;1,400;1,700&family=Karla:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />
</head>
<body>
    <div class="container">
        <div class="loading-screen inactive">
            <img src="img/loadingIcon.svg" alt="Ícone de carregamento">
            <p>Salvando senha...</p>
        </div>
        <form>
            <h1>Cadastre uma nova senha</h1>
            <p class="no_passwords_error inactive">Preencha os campos destacados</p>
            <p class="unequal_passwords_error inactive">As senhas não coincidem</p>
            <p class="response_message inactive"></p>
            <div class="password_container">
                <input type="password" id="password" name="password" placeholder="Nova senha">  
                <button type="button" class="eye-icon">
                    <img src="img/eye.svg" alt="Ícone de olho aberto">
                </button>
            </div>
            <div class="password_container">
                <input type="password" id="passwor_confirm" name="password_confirm" placeholder="Confirme a nova senha">  
                <button type="button" class="eye-icon">
                    <img src="img/eye.svg" alt="Ícone de olho aberto">
                </button>
            </div>
            <button type="button" class="submit_button">Salvar</button>
        </form>
    </div>
    <script src="js/recoverPassword.js"></script>
</body>
</html>