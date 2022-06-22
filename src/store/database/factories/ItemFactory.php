<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $names = ['aaaaa', 'bbbbb', 'ccccc', 'ddddd', 'eeeee', 'fffff'];
        $name = $names[rand(0, count($names) - 1)];

        return [
            'name' => $name,
            'point' => rand(100,1500),
            'user_id' => rand(1,10),
            'description' => 'texttext',
        ];
    }
}
