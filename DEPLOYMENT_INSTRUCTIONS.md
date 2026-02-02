# Инструкции за качване на Reflections функционалността

## Стъпка 1: Качете файловете на VPS

Качете следните файлове/папки:

### Backend файлове:
- `src/Elearning/CoursesBundle/Entity/Reflection.php`
- `src/Elearning/CoursesBundle/Entity/ReflectionRepository.php`
- `src/Elearning/CoursesBundle/Controller/StudentReflectionController.php`
- `src/Elearning/CoursesBundle/Controller/TeacherReflectionController.php`
- `src/Elearning/CoursesBundle/Form/ReflectionType.php`
- `src/Elearning/CoursesBundle/Form/ReflectionResponseType.php`

### Frontend файлове:
- `src/Elearning/CoursesBundle/Resources/views/Reflection/` (цялата папка)

### Конфигурация:
- `app/config/routing.yml`
- `src/AppBundle/Menu/MenuBuilder.php`
- `src/Elearning/UserBundle/Resources/translations/FOSUserBundle.en.yml`

### Скрипт за обновяване:
- `update_database.sh`

## Стъпка 2: Изпълнете скрипта на VPS

```bash
# Направете скрипта изпълним
chmod +x update_database.sh

# Изпълнете го
./update_database.sh
```

## Стъпка 3: Тествайте

Отворете сайта и проверете дали работи нормално.

## Ако има проблем:

Ако продължава да дава 500 error, изпълнете:

```bash
# Проверете какво точно иска да промени Doctrine
php app/console doctrine:schema:update --dump-sql

# Ако изглежда правилно, приложете промените
php app/console doctrine:schema:update --force
```

## Алтернативен метод (ако Doctrine не работи):

Ако `doctrine:schema:update` не работи, можете да изпълните SQL командите ръчно в MySQL:

```bash
mysql -u your_username -p your_database_name < reflection_migration.sql
```
