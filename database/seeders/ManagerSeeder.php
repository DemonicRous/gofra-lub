<?php
// database/seeders/ManagerSeeder.php

namespace Database\Seeders;

use App\Models\Manager;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    public function run(): void
    {
        $managers = [
            [
                'last_name' => 'Зубов',
                'first_name' => 'Дмитрий',
                'patronymic' => 'Евгеньевич',
                'position' => 'Руководитель регионального направления продаж'
            ],
            [
                'last_name' => 'Котмаков',
                'first_name' => 'Сергей',
                'patronymic' => 'Сергеевич',
                'position' => 'Руководитель регионального направления продаж'
            ],
            [
                'last_name' => 'Кургузова',
                'first_name' => 'Юлия',
                'patronymic' => 'Фёдоровна',
                'position' => 'Руководитель регионального направления продаж'
            ],
            [
                'last_name' => 'Мельниченко',
                'first_name' => 'Татьяна',
                'patronymic' => 'Витальевна',
                'position' => 'Менеджер отдела продаж'
            ],
            [
                'last_name' => 'Муминов',
                'first_name' => 'Темур',
                'patronymic' => 'Анварович',
                'position' => 'Руководитель регионального направления продаж'
            ],
            [
                'last_name' => 'Романцов',
                'first_name' => 'Дмитрий',
                'patronymic' => 'Вячеславович',
                'position' => 'Руководитель регионального направления продаж'
            ],
            [
                'last_name' => 'Сумина',
                'first_name' => 'Анна',
                'patronymic' => 'Александровна',
                'position' => 'Менеджер отдела продаж'
            ],
            [
                'last_name' => 'Устинова',
                'first_name' => 'Виктория',
                'patronymic' => 'Александровна',
                'position' => 'Менеджер отдела продаж'
            ],
            [
                'last_name' => 'Франц',
                'first_name' => 'Анастасия',
                'patronymic' => 'Евгеньевна',
                'position' => 'Менеджер отдела продаж'
            ],
            [
                'last_name' => 'Химяк',
                'first_name' => 'Елена',
                'patronymic' => 'Николаевна',
                'position' => 'Руководитель регионального направления продаж'
            ],
            [
                'last_name' => 'Кузьмин',
                'first_name' => 'Олег',
                'patronymic' => 'Анатольевич',
                'position' => 'Руководитель регионального направления продаж'
            ],
            [
                'last_name' => 'Ляксина',
                'first_name' => 'Анна',
                'patronymic' => 'Александровна',
                'position' => 'Дизайнер'
            ],
            [
                'last_name' => 'Чернов',
                'first_name' => 'Александр',
                'patronymic' => 'Алексеевич',
                'position' => 'Технолог-конструктор'
            ],
        ];

        foreach ($managers as $manager) {
            // Формируем полное и короткое имя
            $fullName = trim($manager['last_name'] . ' ' . $manager['first_name'] . ' ' . ($manager['patronymic'] ?? ''));
            $shortName = $manager['last_name'] . ' ' . mb_substr($manager['first_name'], 0, 1) . '.';
            if (!empty($manager['patronymic'])) {
                $shortName .= mb_substr($manager['patronymic'], 0, 1) . '.';
            }

            Manager::firstOrCreate(
                [
                    'last_name' => $manager['last_name'],
                    'first_name' => $manager['first_name']
                ],
                [
                    'last_name' => $manager['last_name'],
                    'first_name' => $manager['first_name'],
                    'patronymic' => $manager['patronymic'] ?? null,
                    'position' => $manager['position'],
                    'full_name' => $fullName,
                    'short_name' => $shortName,
                    'is_active' => true,
                ]
            );
        }
    }
}
