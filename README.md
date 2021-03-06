# Error
**hellpers/error** - Перехват и логирование ошибок и исключений.

## Установка:
	composer require hellpers/error

## Пример:
```php
/*
|-------------------------------------------------------------------------------
| Пример
|-------------------------------------------------------------------------------
|
| Устанавливаем перехватчик ошибок и исключений вызовом статичного метода
| Error::catch().
|
| Метод Error::catch() принимает четыре параметра:
| 1. Строка. Абсолютный путь к корню приложения;
| 2. Строка. Относительный путь к папке для хранения логов внутри приложения;
| 3. Строка. Имя для файла, в который будет происходить запись логов;
| 4. Число. Уровень перехвата ошибок. Например: E_ALL & ~E_NOTICE. По умолчанию
| обрабатываются все возможные ошибки.
|
| При задании имени файла был использован метод Error::d(). Этот метод позволяет
| передать строку, спецсимволы которой будут преобразованы стандартным PHP
| методом DateTime::format(). Таким образом имя файла будет содержать текущую
| дату.
|
*/

Error::catch(
    __DIR__, 'temp/errors', Error::d('Y-m-d') . '.txt', E_ALL & ~E_DEPRECATED
);

throw new Exception('Исключение');
```
## Документация ко всем методам:
Класс Error реализует шаблон singleton инициализирующим методом которого  
является catch(). Возвращаемый объект обладает рядом методов, позволяющих  
настроить поведение перехватчика, ниже их описание.  
  
**d(string $string): string**  
Создать шаблон для преобразования методом DateTime::format().  
Статический метод.  
Порой очень удобно создавать файлы и/или папки, имена которых содержали бы  
элементы даты. Например, для записи логов. Передавать уже готовое название не  
всегда практично, т.к. если скрипт работает продолжительное время и переходит  
из одних суток в другие, тогда название продолжает соответствовать дню  
предыдущему.  
Метод принимает шаблон результирующей строки, как и метод - DateTime::format()  
и возвращает этот же шаблон но обернутый в специальный внутриклассовый, его  
уже можно использовать давая названия папкам и файлам, т.к. обрабатываться  
методом DateTime::format() название будет непосредтвенно в момент создания,  
т.е. будет всегда актуальным.  
  
**errors(int $number, string $text, string $file, int $line): void  
и  
throwables($exception): void**  
Не предназначены для ручного вызова. Реализуют методы-перехватчики для PHP  
функций: set_error_handler() и set_exception_handler(). Если необходимо  
изменить формат отображение информации об ошибке, их можно переопределить.  
  
**stop(bool $switch): self**  
Остановливать или нет выполнение скрипта при перехвате ошибки или исключения.  
  
**date(string $template): self**  
Строка, которая преобразовывается стандартным PHP методом DateTime::format().  
Позволяет установить необходимый формат отображения времени, когда был сделан  
лог.  
  
**timezone(int $hours): self**  
Принимает целое число (как отрицательное, так и положительное). Позволяет  
установить псевдо временную зону. Прибавляет указанное число к часам метода  
date().  
  
**show(bool $switch): self**  
Включить/отключить вывод логов в консоль.  
  
**write(bool $switch): self**  
Включить/отключить логирование в файл  
  
**mail(string $mail, string $from, string $subject): self**  
Позволяет настроить отправку уведомлений на электронную почту. При рассылки  
используется нативная PHP функция - mail().  
  
**before(string $str): self и after(string $str): self**  
Позволяет изменить строки, которыми обрамлено сообщение в начале и конце  
текста. Удобно, если необходимо разделять каждое сообщение лога, например  
переносами строк: \n, PHP_EOL и т.д.
