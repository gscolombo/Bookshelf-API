const form = document.querySelector("form");
const inputs = Array.from(document.querySelectorAll("input"));
const submitButton = form.querySelector("button.submit_button");
const eyeButton = form.querySelectorAll("button.eye-icon");
const loadingScreen = document.querySelector(".loading-screen");
const responseMessage = document.querySelector(".response_message");
const errorMessages = Object.fromEntries(
  Array.from(document.querySelectorAll("p[class*='error']")).map((p) => [
    p.classList[0],
    p,
  ])
);

function showPassword(e) {
  const btn = e.currentTarget;
  const img = btn.querySelector("img");
  const input = btn.parentElement.querySelector("input");

  switch (input.type) {
    case "password":
      input.type = "text";
      img.src = "./img/eye-off.svg";
      img.alt = "Closed eye icon";
      break;
    case "text":
      input.type = "password";
      img.src = "./img/eye.svg";
      img.alt = "Eye icon";
      break;
  }
}

function validatePasswords(password, confirmPassword) {
  for (let error in errorMessages) {
    errorMessages[error].classList.add("inactive");
  }

  inputs.forEach((input) => input.classList.remove("invalid"));

  if (password === "" || confirmPassword === "") {
    errorMessages.no_passwords_error.classList.remove("inactive");
    inputs.forEach((input) =>
      input.value === ""
        ? input.classList.add("invalid")
        : input.classList.remove("invalid")
    );
    return false;
  }

  if (password !== confirmPassword) {
    errorMessages.unequal_passwords_error.classList.remove("inactive");
    inputs.forEach((input) => input.classList.add("invalid"));
    return false;
  }

  return true;
}

async function handleSubmit() {
  const [password, confirmPassword] = inputs;

  if (validatePasswords(password.value, confirmPassword.value)) {
    loadingScreen.classList.remove("inactive");
    const id = location.search.match(/=(\d*)$/)[1];

    const options = {
      method: "POST",
      headers: {
        "Content-Type": "application/json;charset=utf-8",
      },
      body: JSON.stringify({ id: id, password: password.value }),
    };

    const res = await fetch(
      location.origin + "/api/users/save_new_password",
      options
    );
    loadingScreen.classList.add("inactive");
    notify(res);
  }
}

async function notify(response) {
  responseMessage.classList.remove("inactive");

  if (response.ok) {
    responseMessage.innerHTML =
      "Senha atualizada com sucesso! <br>Você já pode fechar essa página :)";
  } else {
    responseMessage.innerHTML =
      "Erro ao salvar senha! <br> Por favor, entre em contato com <b>contact.bookshelf.app@gmail.com</b>";
  }
}

submitButton.addEventListener("click", handleSubmit);
eyeButton.forEach((btn) => btn.addEventListener("click", showPassword));
