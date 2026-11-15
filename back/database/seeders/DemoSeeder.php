<?php

namespace Database\Seeders;

use App\Application\Inventory\Dto\RegisterBookInput;
use App\Application\Inventory\Dto\RegisterCopyInput;
use App\Application\Inventory\Dto\RegisterShelfInput;
use App\Application\Inventory\RegisterBook;
use App\Application\Inventory\RegisterCopy;
use App\Application\Inventory\RegisterShelf;
use App\Application\Loans\Dto\RegisterLoanInput;
use App\Application\Loans\Dto\RegisterReaderInput;
use App\Application\Loans\RegisterLoan;
use App\Application\Loans\RegisterReader;
use App\Application\Ports\Clock;
use App\Infrastructure\Persistence\Models\ShelfModel;
use App\Infrastructure\Persistence\Models\UserModel;
use DateTimeImmutable;
use Illuminate\Database\Seeder;

/** Demo data (local only). Goes through the use cases so business rules apply. */
class DemoSeeder extends Seeder
{
    private const BOOKS = [
        ['9789580000001', 'Cien años de soledad', 'Gabriel García Márquez', 'Sudamericana', 1967, 'NARRATIVE', 23, 15, 3.2],
        ['9789580000002', 'La vorágine', 'José Eustasio Rivera', 'Cromos', 1924, 'NARRATIVE', 21, 14, 2.4],
        ['9789580000003', 'Pedagogía del oprimido', 'Paulo Freire', 'Siglo XXI', 1970, 'ESSAY', 21, 14, 1.8],
        ['9789580000004', 'La educación encierra un tesoro', 'Jacques Delors', 'UNESCO', 1996, 'ESSAY', 24, 17, 1.5],
        ['9789580000005', 'Veinte poemas de amor', 'Pablo Neruda', 'Nascimento', 1924, 'POETRY', 18, 12, 0.8],
        ['9789580000006', 'Antología poética', 'José Asunción Silva', 'Norma', 1996, 'POETRY', 20, 13, 1.2],
        ['9789580000007', 'Rin rin renacuajo', 'Rafael Pombo', 'Panamericana', 1867, 'CHILDREN', 28, 22, 0.6],
        ['9789580000008', 'El principito', 'Antoine de Saint-Exupéry', 'Reynal & Hitchcock', 1943, 'CHILDREN', 23, 16, 1.0],
    ];

    private const READERS = [
        ['1020304050', 'Ana María Ruiz', 'Calle 10 # 5-20'],
        ['1030405060', 'Luis Fernando Gómez', 'Carrera 7 # 45-10'],
        ['1040506070', 'Camila Torres', null],
    ];

    public function run(): void
    {
        if (ShelfModel::exists()) {
            return;
        }
        $user = UserModel::where('login', 'librarian')->value('id');

        foreach (['E-01', 'E-02'] as $code) {
            app(RegisterShelf::class)->execute(new RegisterShelfInput($code, [
                ['number' => 1, 'lengthCm' => 90, 'heightCm' => 32],
                ['number' => 2, 'lengthCm' => 90, 'heightCm' => 32],
                ['number' => 3, 'lengthCm' => 90, 'heightCm' => 26],
            ], $user));
        }

        foreach (self::BOOKS as [$isbn, $title, $author, $publisher, $year, $genre, $height, $width, $thickness]) {
            app(RegisterBook::class)->execute(new RegisterBookInput($isbn, $title, $author, $publisher, $year, $genre, $height, $width, $thickness, $user));
        }

        // 20 copies: 3, 3, 3, 3, 2, 2, 2, 2 per book; 28 cm books go to 32 cm levels
        $copiesPerBook = [3, 3, 3, 3, 2, 2, 2, 2];
        $n = 0;
        foreach (self::BOOKS as $i => [$isbn]) {
            for ($c = 1; $c <= $copiesPerBook[$i]; $c++) {
                $n++;
                app(RegisterCopy::class)->execute(new RegisterCopyInput(
                    $isbn, sprintf('EJ-%04d', $n), $n <= 10 ? 'E-01' : 'E-02', ($n % 2) + 1, $user,
                ));
            }
        }

        foreach (self::READERS as [$identification, $name, $address]) {
            app(RegisterReader::class)->execute(new RegisterReaderInput($identification, $name, $address, $user));
        }

        // One overdue loan: registered with a clock 20 days back (due 5 days ago)
        app()->instance(Clock::class, new class implements Clock
        {
            public function today(): DateTimeImmutable
            {
                return new DateTimeImmutable('today -20 days');
            }
        });
        app(RegisterLoan::class)->execute(new RegisterLoanInput('1030405060', 'EJ-0001', $user));
        app()->forgetInstance(Clock::class);
    }
}
