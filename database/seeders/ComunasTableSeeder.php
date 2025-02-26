<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ComunasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comunas = [
            ['nom_comuna' => 'Arica'],
            ['nom_comuna' => 'Camarones'],
            ['nom_comuna' => 'Putre'],
            ['nom_comuna' => 'General Lagos'],
            ['nom_comuna' => 'Iquique'],
            ['nom_comuna' => 'Alto Hospicio'],
            ['nom_comuna' => 'Pozo Almonte'],
            ['nom_comuna' => 'Camiña'],
            ['nom_comuna' => 'Colchane'],
            ['nom_comuna' => 'Huara'],
            ['nom_comuna' => 'Pica'],
            ['nom_comuna' => 'Antofagasta'],
            ['nom_comuna' => 'Mejillones'],
            ['nom_comuna' => 'Sierra Gorda'],
            ['nom_comuna' => 'Taltal'],
            ['nom_comuna' => 'Calama'],
            ['nom_comuna' => 'Ollagüe'],
            ['nom_comuna' => 'San Pedro de Atacama'],
            ['nom_comuna' => 'Tocopilla'],
            ['nom_comuna' => 'María Elena'],
            ['nom_comuna' => 'Copiapó'],
            ['nom_comuna' => 'Caldera'],
            ['nom_comuna' => 'Tierra Amarilla'],
            ['nom_comuna' => 'Chañaral'],
            ['nom_comuna' => 'Diego de Almagro'],
            ['nom_comuna' => 'Vallenar'],
            ['nom_comuna' => 'Alto del Carmen'],
            ['nom_comuna' => 'Freirina'],
            ['nom_comuna' => 'Huasco'],
            ['nom_comuna' => 'La Serena'],
            ['nom_comuna' => 'Coquimbo'],
            ['nom_comuna' => 'Andacollo'],
            ['nom_comuna' => 'La Higuera'],
            ['nom_comuna' => 'Paiguano'],
            ['nom_comuna' => 'Vicuña'],
            ['nom_comuna' => 'Illapel'],
            ['nom_comuna' => 'Canela'],
            ['nom_comuna' => 'Los Vilos'],
            ['nom_comuna' => 'Salamanca'],
            ['nom_comuna' => 'Ovalle'],
            ['nom_comuna' => 'Combarbalá'],
            ['nom_comuna' => 'Monte Patria'],
            ['nom_comuna' => 'Punitaqui'],
            ['nom_comuna' => 'Río Hurtado'],
            ['nom_comuna' => 'Valparaíso'],
            ['nom_comuna' => 'Casablanca'],
            ['nom_comuna' => 'Concón'],
            ['nom_comuna' => 'Juan Fernández'],
            ['nom_comuna' => 'Puchuncaví'],
            ['nom_comuna' => 'Quintero'],
            ['nom_comuna' => 'Viña del Mar'],
            ['nom_comuna' => 'Isla de Pascua'],
            ['nom_comuna' => 'Los Andes'],
            ['nom_comuna' => 'Calle Larga'],
            ['nom_comuna' => 'Rinconada'],
            ['nom_comuna' => 'San Esteban'],
            ['nom_comuna' => 'La Ligua'],
            ['nom_comuna' => 'Cabildo'],
            ['nom_comuna' => 'Papudo'],
            ['nom_comuna' => 'Petorca'],
            ['nom_comuna' => 'Zapallar'],
            ['nom_comuna' => 'Quillota'],
            ['nom_comuna' => 'La Cruz'],
            ['nom_comuna' => 'La Calera'],
            ['nom_comuna' => 'Hijuelas'],
            ['nom_comuna' => 'Nogales'],
            ['nom_comuna' => 'San Antonio'],
            ['nom_comuna' => 'Algarrobo'],
            ['nom_comuna' => 'Cartagena'],
            ['nom_comuna' => 'El Quisco'],
            ['nom_comuna' => 'El Tabo'],
            ['nom_comuna' => 'Santo Domingo'],
            ['nom_comuna' => 'San Felipe'],
            ['nom_comuna' => 'Catemu'],
            ['nom_comuna' => 'Llaillay'],
            ['nom_comuna' => 'Panquehue'],
            ['nom_comuna' => 'Putaendo'],
            ['nom_comuna' => 'Santa María'],
            ['nom_comuna' => 'Quilpué'],
            ['nom_comuna' => 'Limache'],
            ['nom_comuna' => 'Olmué'],
            ['nom_comuna' => 'Villa Alemana'],
            ['nom_comuna' => 'Rancagua'],
            ['nom_comuna' => 'Codegua'],
            ['nom_comuna' => 'Coinco'],
            ['nom_comuna' => 'Coltauco'],
            ['nom_comuna' => 'Doñihue'],
            ['nom_comuna' => 'Graneros'],
            ['nom_comuna' => 'Las Cabras'],
            ['nom_comuna' => 'Machalí'],
            ['nom_comuna' => 'Malloa'],
            ['nom_comuna' => 'Mostazal'],
            ['nom_comuna' => 'Olivar'],
            ['nom_comuna' => 'Peumo'],
            ['nom_comuna' => 'Pichidegua'],
            ['nom_comuna' => 'Quinta de Tilcoco'],
            ['nom_comuna' => 'Rengo'],
            ['nom_comuna' => 'Requínoa'],
            ['nom_comuna' => 'San Vicente'],
            ['nom_comuna' => 'Pichilemu'],
            ['nom_comuna' => 'La Estrella'],
            ['nom_comuna' => 'Litueche'],
            ['nom_comuna' => 'Marchihue'],
            ['nom_comuna' => 'Navidad'],
            ['nom_comuna' => 'Paredones'],
            ['nom_comuna' => 'San Fernando'],
            ['nom_comuna' => 'Chépica'],
            ['nom_comuna' => 'Chimbarongo'],
            ['nom_comuna' => 'Lolol'],
            ['nom_comuna' => 'Nancagua'],
            ['nom_comuna' => 'Palmilla'],
            ['nom_comuna' => 'Peralillo'],
            ['nom_comuna' => 'Placilla'],
            ['nom_comuna' => 'Pumanque'],
            ['nom_comuna' => 'Santa Cruz'],
            ['nom_comuna' => 'Talca'],
            ['nom_comuna' => 'Constitución'],
            ['nom_comuna' => 'Curepto'],
            ['nom_comuna' => 'Empedrado'],
            ['nom_comuna' => 'Maule'],
            ['nom_comuna' => 'Pelarco'],
            ['nom_comuna' => 'Pencahue'],
            ['nom_comuna' => 'Río Claro'],
            ['nom_comuna' => 'San Clemente'],
            ['nom_comuna' => 'San Rafael'],
            ['nom_comuna' => 'Cauquenes'],
            ['nom_comuna' => 'Chanco'],
            ['nom_comuna' => 'Pelluhue'],
            ['nom_comuna' => 'Curicó'],
            ['nom_comuna' => 'Hualañé'],
            ['nom_comuna' => 'Licantén'],
            ['nom_comuna' => 'Molina'],
            ['nom_comuna' => 'Rauco'],
            ['nom_comuna' => 'Romeral'],
            ['nom_comuna' => 'Sagrada Familia'],
            ['nom_comuna' => 'Teno'],
            ['nom_comuna' => 'Vichuquén'],
            ['nom_comuna' => 'Linares'],
            ['nom_comuna' => 'Colbún'],
            ['nom_comuna' => 'Longaví'],
            ['nom_comuna' => 'Parral'],
            ['nom_comuna' => 'Retiro'],
            ['nom_comuna' => 'San Javier'],
            ['nom_comuna' => 'Villa Alegre'],
            ['nom_comuna' => 'Yerbas Buenas'],
            ['nom_comuna' => 'Concepción'],
            ['nom_comuna' => 'Coronel'],
            ['nom_comuna' => 'Chiguayante'],
            ['nom_comuna' => 'Florida'],
            ['nom_comuna' => 'Hualqui'],
            ['nom_comuna' => 'Lota'],
            ['nom_comuna' => 'Penco'],
            ['nom_comuna' => 'San Pedro de la Paz'],
            ['nom_comuna' => 'Santa Juana'],
            ['nom_comuna' => 'Talcahuano'],
            ['nom_comuna' => 'Tomé'],
            ['nom_comuna' => 'Hualpén'],
            ['nom_comuna' => 'Lebu'],
            ['nom_comuna' => 'Arauco'],
            ['nom_comuna' => 'Cañete'],
            ['nom_comuna' => 'Contulmo'],
            ['nom_comuna' => 'Curanilahue'],
            ['nom_comuna' => 'Los Álamos'],
            ['nom_comuna' => 'Tirúa'],
            ['nom_comuna' => 'Los Ángeles'],
            ['nom_comuna' => 'Antuco'],
            ['nom_comuna' => 'Cabrero'],
            ['nom_comuna' => 'Laja'],
            ['nom_comuna' => 'Mulchén'],
            ['nom_comuna' => 'Nacimiento'],
            ['nom_comuna' => 'Negrete'],
            ['nom_comuna' => 'Quilaco'],
            ['nom_comuna' => 'Quilleco'],
            ['nom_comuna' => 'San Rosendo'],
            ['nom_comuna' => 'Santa Bárbara'],
            ['nom_comuna' => 'Tucapel'],
            ['nom_comuna' => 'Yumbel'],
            ['nom_comuna' => 'Alto Biobío'],
            ['nom_comuna' => 'Temuco'],
            ['nom_comuna' => 'Carahue'],
            ['nom_comuna' => 'Cunco'],
            ['nom_comuna' => 'Curarrehue'],
            ['nom_comuna' => 'Freire'],
            ['nom_comuna' => 'Galvarino'],
            ['nom_comuna' => 'Gorbea'],
            ['nom_comuna' => 'Lautaro'],
            ['nom_comuna' => 'Loncoche'],
            ['nom_comuna' => 'Melipeuco'],
            ['nom_comuna' => 'Nueva Imperial'],
            ['nom_comuna' => 'Padre Las Casas'],
            ['nom_comuna' => 'Perquenco'],
            ['nom_comuna' => 'Pitrufquén'],
            ['nom_comuna' => 'Pucón'],
            ['nom_comuna' => 'Saavedra'],
            ['nom_comuna' => 'Teodoro Schmidt'],
            ['nom_comuna' => 'Toltén'],
            ['nom_comuna' => 'Vilcún'],
            ['nom_comuna' => 'Villarrica'],
            ['nom_comuna' => 'Cholchol'],
            ['nom_comuna' => 'Angol'],
            ['nom_comuna' => 'Collipulli'],
            ['nom_comuna' => 'Curacautín'],
            ['nom_comuna' => 'Ercilla'],
            ['nom_comuna' => 'Lonquimay'],
            ['nom_comuna' => 'Los Sauces'],
            ['nom_comuna' => 'Lumaco'],
            ['nom_comuna' => 'Purén'],
            ['nom_comuna' => 'Renaico'],
            ['nom_comuna' => 'Traiguén'],
            ['nom_comuna' => 'Victoria'],
            ['nom_comuna' => 'Valdivia'],
            ['nom_comuna' => 'Corral'],
            ['nom_comuna' => 'Lanco'],
            ['nom_comuna' => 'Los Lagos'],
            ['nom_comuna' => 'Máfil'],
            ['nom_comuna' => 'Mariquina'],
            ['nom_comuna' => 'Paillaco'],
            ['nom_comuna' => 'Panguipulli'],
            ['nom_comuna' => 'La Unión'],
            ['nom_comuna' => 'Futrono'],
            ['nom_comuna' => 'Lago Ranco'],
            ['nom_comuna' => 'Río Bueno'],
            ['nom_comuna' => 'Puerto Montt'],
            ['nom_comuna' => 'Calbuco'],
            ['nom_comuna' => 'Cochamó'],
            ['nom_comuna' => 'Fresia'],
            ['nom_comuna' => 'Frutillar'],
            ['nom_comuna' => 'Los Muermos'],
            ['nom_comuna' => 'Llanquihue'],
            ['nom_comuna' => 'Maullín'],
            ['nom_comuna' => 'Puerto Varas'],
            ['nom_comuna' => 'Castro'],
            ['nom_comuna' => 'Ancud'],
            ['nom_comuna' => 'Chonchi'],
            ['nom_comuna' => 'Curaco de Vélez'],
            ['nom_comuna' => 'Dalcahue'],
            ['nom_comuna' => 'Puqueldón'],
            ['nom_comuna' => 'Queilén'],
            ['nom_comuna' => 'Quellón'],
            ['nom_comuna' => 'Quemchi'],
            ['nom_comuna' => 'Quinchao'],
            ['nom_comuna' => 'Osorno'],
            ['nom_comuna' => 'Puerto Octay'],
            ['nom_comuna' => 'Purranque'],
            ['nom_comuna' => 'Puyehue'],
            ['nom_comuna' => 'Río Negro'],
            ['nom_comuna' => 'San Juan de la Costa'],
            ['nom_comuna' => 'San Pablo'],
            ['nom_comuna' => 'Chaitén'],
            ['nom_comuna' => 'Futaleufú'],
            ['nom_comuna' => 'Hualaihué'],
            ['nom_comuna' => 'Palena'],
            ['nom_comuna' => 'Coyhaique'],
            ['nom_comuna' => 'Lago Verde'],
            ['nom_comuna' => 'Aysén'],
            ['nom_comuna' => 'Cisnes'],
            ['nom_comuna' => 'Guaitecas'],
            ['nom_comuna' => 'Cochrane'],
            ['nom_comuna' => 'O\'Higgins'],
            ['nom_comuna' => 'Tortel'],
            ['nom_comuna' => 'Chile Chico'],
            ['nom_comuna' => 'Río Ibáñez'],
            ['nom_comuna' => 'Punta Arenas'],
            ['nom_comuna' => 'Laguna Blanca'],
            ['nom_comuna' => 'Río Verde'],
            ['nom_comuna' => 'San Gregorio'],
            ['nom_comuna' => 'Cabo de Hornos'],
            ['nom_comuna' => 'Antártica'],
            ['nom_comuna' => 'Porvenir'],
            ['nom_comuna' => 'Primavera'],
            ['nom_comuna' => 'Timaukel'],
            ['nom_comuna' => 'Natales'],
            ['nom_comuna' => 'Torres del Paine'],
            ['nom_comuna' => 'Santiago'],
            ['nom_comuna' => 'Cerrillos'],
            ['nom_comuna' => 'Cerro Navia'],
            ['nom_comuna' => 'Conchalí'],
            ['nom_comuna' => 'El Bosque'],
            ['nom_comuna' => 'Estación Central'],
            ['nom_comuna' => 'Huechuraba'],
            ['nom_comuna' => 'Independencia'],
            ['nom_comuna' => 'La Cisterna'],
            ['nom_comuna' => 'La Florida'],
            ['nom_comuna' => 'La Granja'],
            ['nom_comuna' => 'La Pintana'],
            ['nom_comuna' => 'La Reina'],
            ['nom_comuna' => 'Las Condes'],
            ['nom_comuna' => 'Lo Barnechea'],
            ['nom_comuna' => 'Lo Espejo'],
            ['nom_comuna' => 'Lo Prado'],
            ['nom_comuna' => 'Macul'],
            ['nom_comuna' => 'Maipú'],
            ['nom_comuna' => 'Ñuñoa'],
            ['nom_comuna' => 'Pedro Aguirre Cerda'],
            ['nom_comuna' => 'Peñalolén'],
            ['nom_comuna' => 'Providencia'],
            ['nom_comuna' => 'Pudahuel'],
            ['nom_comuna' => 'Quilicura'],
            ['nom_comuna' => 'Quinta Normal'],
            ['nom_comuna' => 'Recoleta'],
            ['nom_comuna' => 'Renca'],
            ['nom_comuna' => 'San Joaquín'],
            ['nom_comuna' => 'San Miguel'],
            ['nom_comuna' => 'San Ramón'],
            ['nom_comuna' => 'Vitacura'],
            ['nom_comuna' => 'Puente Alto'],
            ['nom_comuna' => 'Pirque'],
            ['nom_comuna' => 'San José de Maipo'],
            ['nom_comuna' => 'Colina'],
            ['nom_comuna' => 'Lampa'],
            ['nom_comuna' => 'Tiltil'],
            ['nom_comuna' => 'San Bernardo'],
            ['nom_comuna' => 'Buin'],
            ['nom_comuna' => 'Calera de Tango'],
            ['nom_comuna' => 'Paine'],
            ['nom_comuna' => 'Melipilla'],
            ['nom_comuna' => 'Alhué'],
            ['nom_comuna' => 'Curacaví'],
            ['nom_comuna' => 'María Pinto'],
            ['nom_comuna' => 'San Pedro'],
            ['nom_comuna' => 'Talagante'],
            ['nom_comuna' => 'El Monte'],
            ['nom_comuna' => 'Isla de Maipo'],
            ['nom_comuna' => 'Padre Hurtado'],
            ['nom_comuna' => 'Peñaflor'],
        ];

        DB::table('comunas')->insert($comunas);
    }
}
