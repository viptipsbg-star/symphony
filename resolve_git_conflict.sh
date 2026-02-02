#!/bin/bash

# Resolve Git conflicts by keeping local files
echo "Resolving Git merge conflicts..."

# On Windows PowerShell, run these commands:

# 1. Keep all local Reflection files
git add src/Elearning/CoursesBundle/Entity/Reflection.php
git add src/Elearning/CoursesBundle/Entity/ReflectionRepository.php
git add src/Elearning/CoursesBundle/Controller/StudentReflectionController.php
git add src/Elearning/CoursesBundle/Controller/TeacherReflectionController.php
git add src/Elearning/CoursesBundle/Form/ReflectionType.php
git add src/Elearning/CoursesBundle/Form/ReflectionResponseType.php
git add src/Elearning/CoursesBundle/Resources/views/Reflection/

# 2. Commit the merge
git commit -m "Resolve merge conflict - keep Reflection feature files"

# 3. Push to remote
git push origin main

echo "Done! Now run on VPS:"
echo "cd /var/www/html"
echo "git pull origin main"
echo "rm -rf app/cache/*"
