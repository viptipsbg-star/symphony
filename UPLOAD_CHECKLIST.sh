#!/bin/bash

# Upload all Reflection files to VPS
echo "Uploading Reflection feature files..."

# Controllers
echo "Uploading controllers..."
# You need to upload these files:
# - src/Elearning/CoursesBundle/Controller/StudentReflectionController.php
# - src/Elearning/CoursesBundle/Controller/TeacherReflectionController.php

# Entities
echo "Uploading entities..."
# - src/Elearning/CoursesBundle/Entity/Reflection.php
# - src/Elearning/CoursesBundle/Entity/ReflectionRepository.php

# Forms
echo "Uploading forms..."
# - src/Elearning/CoursesBundle/Form/ReflectionType.php
# - src/Elearning/CoursesBundle/Form/ReflectionResponseType.php

# Views
echo "Uploading views..."
# - src/Elearning/CoursesBundle/Resources/views/Reflection/ (entire directory)

# Translations - THIS IS CRITICAL!
echo "Uploading translations..."
# - src/Elearning/UserBundle/Resources/translations/FOSUserBundle.en.yml

# Config
echo "Uploading config..."
# - app/config/routing.yml
# - src/AppBundle/Menu/MenuBuilder.php

echo "After uploading, run on VPS:"
echo "rm -rf app/cache/*"
echo "php app/console doctrine:schema:update --force"
