<?php

namespace Hellpers;

use Exception;

class Error
{
    /**
     * @var self|null Объект текущего класса
     */
    private static $instance = null;

    /**
     * @var string Корень приложения
     */
    private $core = '';

    /**
     * @var string Путь к папке хранения логов
     */
    private $path = '';

    /**
     * @var type Имя файла, в который будет писаться лог
     */
    private $file = '';

    /**
     * @var bool Останавливать или нет работу скрипта при перехвате ошибки
     */
    private $stop = true;

    /**
     * @var object|null Объект класса Logger
     */
    private $logger = null;

    /**
     * Приватим конструктор. Устанавливаем перехватчики для ошибок и исключений
     */
    private function __construct()
    {
        set_error_handler([$this, 'errors']);
        set_exception_handler([$this, 'throwables']);
    }

    /**
     * Приватим метод клонирования
     */
    private function __clone() {}

    /**
     * Запуск перехватчика
     * 
     * @param string $core Корень приложения (абсолютный путь)
     * @param string $path Путь относительно корня приложения
     * @param string $file Имя файла для записи лога
     * @return self Модифицированный текущий объект
     */
    public static function catch(string $core, string $path, string $file): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            self::$instance->core = $core;
            self::$instance->path = $path;
            self::$instance->file = $file;

            self::$instance->logger = new Logger(self::$instance->core);

            self::$instance->logger = self::$instance->logger
                ->path(self::$instance->path)->file(self::$instance->file)
                ->date(
                    '[H:i:s.u - d.m.Y]' . PHP_EOL
                        . '----------------------------------------------------'
                        . '----------------------------' . PHP_EOL
                );
        }

        unset($core, $path, $file);

        return self::$instance;
    }

    /**
     * Создать шаблон для преобразования методом DateTime::format()
     * 
     * @param string $string Строка содержащая спецсиволы
     * @return string Строка обернутая шаблоном для декодирования
     */
    public static function d(string $string): string
    {
        return Logger::d($string);
    }

    /**
     * Обработчик ошибок
     * 
     * @param int $number Номер ошибки
     * @param string $text Сообщение об ошибке
     * @param string $file Файл, в котором произошла ошибка
     * @param int $line Строка, на которой произошла ошибка
     * @return void
     */
    public function errors(
        int $number, string $text, string $file, int $line
    ): void
    {
        $message = '';

        $message .= "Тип: Ошибка #$number" . PHP_EOL;
        $message .= "Сообщение: $text" . PHP_EOL;
        $message .= "Файл: $file" . PHP_EOL;
        $message .= "Строка: $line";

        $this->logger->send($message);

        unset($number, $message, $file, $line, $message);

        if ($this->stop) {
            exit;
        }
    }

    /**
     * Обработчик исключений
     * 
     * @param $exception Исключение
     * @return void
     */
    public function throwables($exception): void
    {
        $message = '';
        $stack   = [];
        $trace   = null;

        $message .= 'Тип: Исключение "' . get_class($exception) . '"' . PHP_EOL;
        $message .= 'Код: ' . $exception->getCode() . PHP_EOL;
        $message .= 'Сообщение: ' . $exception->getMessage() . PHP_EOL;
        $message .= 'Файл: ' . $exception->getFile() . PHP_EOL;
        $message .= 'Строка: ' . $exception->getLine() . PHP_EOL;
        $message .= "Трассировка:" . PHP_EOL;

        $stack = explode("\n", $exception->getTraceAsString());

        foreach ($stack as $trace) {
            $message .= "    $trace" . PHP_EOL;
        }

        $message = rtrim($message);

        $this->logger->send($message);

        unset($exception, $message, $stack, $trace);

        if ($this->stop) {
            exit;
        }
    }

    /**
     * Остановка скрипта при перехвате ошибки или исключения
     * 
     * @param bool $switch true - останавливать, false - продолжать работу
     * @return self Модифицированный текущий объект
     */
    public function stop(bool $switch): self
    {
        $this->stop = $switch;

        unset($switch);

        return $this;
    }

    /**
     * Позволяет установить необходимый формат отображения времени, когда был
     * сделан лог
     * 
     * @param string $template Строка, для преобразования стандартным PHP
     * методом DateTime::format()
     * @return self Модифицированный текущий объект
     */
    public function date(string $template): self
    {
        $this->logger = $this->logger->date($template);

        unset($template);

        return $this;
    }

    /**
     * Позволяет установить псевдо временную зону. Прибавляет указанное число к
     * часам метода self::date()
     * 
     * @param int $hours Часы - целое число (как отрицательное, так и
     * положительное)
     * @return self Модифицированный текущий объект
     */
    public function timezone(int $hours): self
    {
        $this->logger = $this->logger->timezone($hours);

        unset($hours);

        return $this;
    }

    /**
     * Включить/отключить вывод логов в консоль
     * 
     * @param bool $switch true/false - включить/отключить
     * @return self Модифицированный текущий объект
     */
    public function show(bool $switch): self
    {
        $this->logger = $this->logger->console($switch);

        unset($switch);

        return $this;
    }

    /**
     * Включить/отключить логирование в файл
     * 
     * @param bool $switch true/false - включить/отключить
     * @return self Модифицированный текущий объект
     */
    public function write(bool $switch): self
    {
        $file = '';

        if ($switch === true) {
            $file = $this->file;
        }

        $this->logger = $this->logger->file($file);

        unset($switch, $file);

        return $this;
    }

    /**
     * Позволяет настроить отправку уведомлений на электронную почту
     * 
     * @param string $mail Адрес получателя
     * @param string $from Адрес отправителя
     * @param string $subject Тема письма
     * @return self Модифицированный текущий объект
     */
    public function mail(string $mail, string $from, string $subject): self
    {
        $this->logger = $this->logger->mail($mail)->from($from)
            ->subject($subject);

        unset($mail, $from, $subject);

        return $this;
    }

    /**
     * Установить обромляющую строку перед сообщением лога
     * 
     * @param string $str Строка перед сообщением лога
     * @return self Модифицированный текущий объект
     */
    public function before(string $str): self
    {
        $this->logger = $this->logger->before($str);

        unset($str);

        return $this;
    }

    /**
     * Установить обромляющую строку после сообщением лога
     * 
     * @param string $str Строка после сообщением лога
     * @return self Модифицированный текущий объект
     */
    public function after(string $str): self
    {
        $this->logger = $this->logger->after($str);

        unset($str);

        return $this;
    }
}
