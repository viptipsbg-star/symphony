#!/bin/bash

# ВАЖНО: Изпълнете това на VPS след качване на файловете

echo "=== Изтриване на всички Reflection файлове ==="

# Изтрийте всички нови файлове
rm -f src/Elearning/CoursesBundle/Entity/Reflection.php
rm -f src/Elearning/CoursesBundle/Entity/ReflectionRepository.php
rm -f src/Elearning/CoursesBundle/Controller/StudentReflectionController.php
rm -f src/Elearning/CoursesBundle/Controller/TeacherReflectionController.php
rm -f src/Elearning/CoursesBundle/Form/ReflectionType.php
rm -f src/Elearning/CoursesBundle/Form/ReflectionResponseType.php
rm -rf src/Elearning/CoursesBundle/Resources/views/Reflection

echo "=== Изчистване на кеша ==="
rm -rf app/cache/*

echo "=== Готово! Сайтът трябва да работи сега ==="
