/*
 * Message.js
 * 		bagam toate textele folosite in JS aici
 */

var message = function message(what) {
	if (!message.lang)
		message.lang = 'ro';



	var i = (message.lang=='ro')?0:1;
	var m = {};//translation object

	//bbcodes messages
	m.anchor = ['Introduceți numele ancorei', 'Введите имя анкоры'];
	m.spoiler = ['Text în spoiler', 'Текст спрятанный под спойлером'];
	m.link = ['Text sub adresă web', 'Имя веб адреса'];
	m.size = ['Mărimea textului', 'Размер шрифта'];
	m.infopreview = ['Efectuați click drept pentru a închide fereastra', 'Щелкните правой кнопкой мыши, чтобы закрыть окно'];


	//menus messages
	m.err_enter_username = ['Va rugăm să introduceţi username-ul.', 'Просим вас ввести имя пользователя.'];
	m.err_enter_password = ['Va rugăm să introduceţi parola.', 'Просим вас ввести пароль.'];
	m.err_wrong_username = ['Eroare! Username-ul este incorect.', 'Ошибка! Неправильное имя пользывателя.'];
	m.err_wrong_password = ['Eroare! Parola este greşită.', 'Ошибка! Неправильный пароль.'];
	m.info_captcha_login = ['Este necesară o verificare suplimentară, <br/>redirecționare..', 'Необходим дополнительный контроль, <br/>перенаправление..'];
	m.ok_logined_successfully = ['Autentificare reuşită!', 'Авторизация прошла успешно!'];

	//
	m.lang_click_here = ['apasă aici', 'нажмите сюда'];

	//browse
	m.Denumirea = ['Denumirea', 'Название'];

    return m[what][i];
};

