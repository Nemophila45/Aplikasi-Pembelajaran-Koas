<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'no_rekam_medis' => $this->faker->unique()->regexify('RM-[0-9]{4}'),
            'nik' => $this->faker->unique()->numerify('32##############'),
            'nama' => $this->faker->name(),
            'tanggal_lahir' => $this->faker
                ->dateTimeBetween('-80 years', '-18 years')
                ->format('Y-m-d'),
            'alamat' => $this->faker->streetAddress(),
            'provinsi' => $this->faker->state(),
            'kabupaten_kota' => $this->faker->city(),
            'kecamatan' => $this->faker->citySuffix(),
            'kelurahan_desa' => $this->faker->streetName(),
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'no_hp' => $this->faker->phoneNumber(),
        ];
    }
}
