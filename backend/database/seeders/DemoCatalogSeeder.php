<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Copy;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dades de demostració del catàleg (només entorn local): usuaris de diverses ciutats,
 * llibres en els quatre idiomes i exemplars amb estats, modalitats i estats de conservació variats.
 * Serveix per provar la cerca, els filtres, l'ordre i la paginació.
 *
 * Es pot executar més d'una vegada: no duplica usuaris, llibres ni exemplars.
 */
class DemoCatalogSeeder extends Seeder
{
    /** Contrasenya dels usuaris de demostració (12 caràcters, com exigeix el registre). */
    private const PASSWORD = 'contrasenya123';

    public function run(): void
    {
        $owners = collect([
            ['Marta Rius', 'marta@unshelf.test', 'Barcelona', '08012'],
            ['Jordi Puig', 'jordi@unshelf.test', 'Girona', '17001'],
            ['Laia Ferrer', 'laia@unshelf.test', 'Lleida', '25002'],
            ['Pau Soler', 'pau@unshelf.test', 'Tarragona', '43001'],
        ])->map(fn (array $u) => User::firstOrCreate(
            ['email' => $u[1]],
            ['name' => $u[0], 'password' => self::PASSWORD, 'city' => $u[2], 'postal_code' => $u[3]],
        ));

        $genres = Genre::pluck('id_genre', 'slug');

        // [títol, autor, idioma, gènere, sinopsi]
        $books = [
            ['La plaça del Diamant', 'Mercè Rodoreda', 'catala', 'novella', "La Natàlia, a qui tothom diu Colometa, explica la seva vida a la Barcelona d'abans, durant i després de la Guerra Civil. És la història d'una dona que intenta sobreviure en un món que canvia massa de pressa, contada amb una veu senzilla i plena de detalls quotidians. El barri de Gràcia, les seves places i els seus coloms acompanyen una narració sobre la pèrdua, la fam, la soledat i, finalment, la possibilitat de tornar a començar."],
            ['Mirall trencat', 'Mercè Rodoreda', 'catala', 'novella', "La història d'una família benestant de Barcelona al llarg de tres generacions, i de la casa on viuen."],
            ['Solitud', 'Víctor Català', 'catala', 'novella', "La Mila arriba a una ermita de muntanya amb el seu marit i hi descobreix l'aïllament i la força del paisatge."],
            ['Jo confesso', 'Jaume Cabré', 'catala', 'novella', "L'Adrià Ardèvol repassa la seva vida i la història d'un violí antic que travessa segles de culpa i de bellesa. Una novel·la ambiciosa que salta d'una època a l'altra, de la Inquisició a la Barcelona de la postguerra, i que es pregunta si és possible conviure amb el mal que han fet els altres i amb el que hem fet nosaltres mateixos."],
            ['Les veus del Pamano', 'Jaume Cabré', 'catala', 'historica', 'Una mestra descobreix en un poble del Pallars una història amagada de la postguerra.'],
            ['Pa negre', 'Emili Teixidor', 'catala', 'novella', "L'Andreu creix a la Catalunya rural de la postguerra, entre la misèria i els secrets dels adults."],
            ['El temps de les cireres', 'Montserrat Roig', 'catala', 'novella', 'La Natàlia torna a Barcelona després de dotze anys fora i retroba la seva família i la ciutat.'],
            ['Terra baixa', 'Àngel Guimerà', 'catala', 'teatre', 'En Manelic baixa de la muntanya per casar-se amb la Marta, sense saber el que s\'amaga darrere del casament.'],
            ['Tirant lo Blanc', 'Joanot Martorell', 'catala', 'historica', 'Les aventures del cavaller Tirant, de les justes a Anglaterra fins a l\'imperi de Constantinoble.'],
            ['Antologia poètica', 'Miquel Martí i Pol', 'catala', 'poesia', 'Una selecció de poemes sobre la vida quotidiana, la malaltia, el país i l\'amor.'],
            ['Nada', 'Carmen Laforet', 'castella', 'novella', 'Andrea llega a Barcelona para estudiar y se instala en el piso oscuro y opresivo de su familia.'],
            ['Cien años de soledad', 'Gabriel García Márquez', 'castella', 'novella', 'La historia de la familia Buendía y del pueblo de Macondo a lo largo de siete generaciones.'],
            ['Sapiens. De animales a dioses', 'Yuval Noah Harari', 'castella', 'divulgacio', 'Un recorrido por la historia de la humanidad, desde los primeros sapiens hasta hoy.'],
            ['Manolito Gafotas', 'Elvira Lindo', 'castella', 'infantil', 'Manolito cuenta con humor su vida en el barrio madrileño de Carabanchel.'],
            ['Dune', 'Frank Herbert', 'angles', 'ciencia-ficcio', 'Paul Atreides arrives on the desert planet Arrakis, the only source of the most valuable substance in the universe.'],
            ['The Hobbit', 'J. R. R. Tolkien', 'angles', 'fantasia', 'Bilbo Baggins leaves his comfortable home to join a company of dwarves on a quest for a dragon\'s treasure.'],
            ['Le Petit Prince', 'Antoine de Saint-Exupéry', 'frances', 'infantil', 'Un aviateur perdu dans le désert rencontre un petit prince venu d\'une autre planète.'],
            ['L\'Étranger', 'Albert Camus', 'frances', 'novella', 'Meursault, un employé de bureau à Alger, raconte les jours qui suivent la mort de sa mère.'],
        ];

        $conditions = ['com_nou', 'bon_estat', 'acceptable'];
        $weeks = [2, 4, 8];
        $notes = [
            'Té el llom una mica gastat. Podem quedar a la plaça del barri.',
            'Com nou, només llegit una vegada.',
            'Té algunes notes a llapis als marges.',
            null,
        ];

        foreach ($books as $i => [$title, $author, $language, $genre, $synopsis]) {
            $book = Book::firstOrCreate(
                ['title' => $title, 'author' => $author],
                ['id_genre' => $genres[$genre], 'language' => $language, 'synopsis' => $synopsis],
            );

            if ($book->copies()->exists()) {
                continue; // ja té exemplars d'una execució anterior
            }

            // D'1 a 3 exemplars per llibre, de propietaris diferents
            $copiesCount = $i % 3 + 1;
            for ($c = 0; $c < $copiesCount; $c++) {
                $n = $i + $c;
                $isLoan = $n % 3 !== 0;

                $copy = new Copy([
                    'id_book' => $book->id_book,
                    'condition' => $conditions[$n % 3],
                    'mode' => $isLoan ? 'prestec' : 'donacio',
                    'max_loan_weeks' => $isLoan ? $weeks[$n % 3] : null,
                    'notes' => $notes[$n % 4],
                ]);
                $copy->id_user_owner = $owners[$n % $owners->count()]->id_user;
                // La majoria disponibles; alguns reservats o en préstec (també visibles al catàleg)
                $copy->status = match ($n % 7) {
                    5 => 'reservat',
                    6 => 'en_prestec',
                    default => 'disponible',
                };
                // Dates escalonades perquè l'ordre «Més recents» es noti
                $copy->created_at = now()->subDays($i * 2 + $c);
                $copy->save();
            }
        }

        $this->demoRequest($owners->first());
    }

    /**
     * Una sol·licitud pendent de l'usuari de proves sobre un exemplar de la Marta,
     * per veure la targeta «Sol·licitat» a la fitxa.
     */
    private function demoRequest(User $owner): void
    {
        $requester = User::where('email', 'test@unshelf.test')->first();
        $copy = Copy::where('id_user_owner', $owner->id_user)->where('status', 'disponible')->first();

        if (! $requester || ! $copy || BookRequest::where('id_user_requester', $requester->id_user)->exists()) {
            return;
        }

        BookRequest::create([
            'id_copy' => $copy->id_copy,
            'id_user_requester' => $requester->id_user,
            'id_user_owner' => $owner->id_user,
            'mode' => $copy->mode,
            'loan_weeks' => $copy->mode === 'prestec' ? $copy->max_loan_weeks : null,
        ]);
    }
}
