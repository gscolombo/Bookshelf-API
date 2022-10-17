<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gentium+Book+Basic&family=Karla:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="views/style/home.css">
    <title>API do Bookshelf</title>
</head>
<body>
    <header>
        <h1>Bookshelf<sub>API</sub></h1>
        <h2>Docs</h2>
    </header>
    <div class="wrapper">
        <aside class="description">
            <p>
                This <strong>RESTFul API</strong> acts as the back-end of a personal project called Bookshelf, a library manager inspired in the social network GoodReads and developed with React.
            </p>
            <p>
                <strong>Developed with PHP</strong>, the API is mainly responsible to mediate the interaction between the user and the database (MySQL) through <strong>CRUD</strong>. Moreover, authentication mechanisms, user registration and related actions are also mediated by the API. <strong>JWT Tokens</strong> are stored and send with HTTP-only cookies, along with the public-key for token decoding when the proper endpoints are requested with valid user data in the requisition body.
            </p>
            <p>
                On the side, you can check a requisition processing flow diagram to return the server response to the client, with the requested endpoint pattern between key steps.
            </p>
            <ul>
                <li>
                    <img src="views/img/icons8-github.svg" alt="GitHub icon">
                    <a target="_blank" href="https://github.com/gscolombo/Bookshelf-API">API GitHub repository</a>
                </li>
                <li>
                    <img src="views/img/icons8-linkedin-circled.svg" alt="LinkedIn icon">
                    <a target="_blank" href="https://www.linkedin.com/in/gabrielscdev/">My LinkedIn profile</a>
                </li>
            </ul>
        </aside>
        <div class="diagram-container">
            <div class="button">
                <p>Toggle fullscreen</p>
                <img src="views/img/fullscreen-svgrepo-com.svg" alt="">
            </div>
            <img class="diagram" src="views/img/bookshelf flow.drawio.svg" alt="diagram">
        </div>
    </div>
    <script src="views/js/home.js"></script>
</body>
</html>