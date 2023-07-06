<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;
use function PHPUnit\Framework\assertTrue;

class CollectionTest extends TestCase
{
    public function testMakeCollection(): void
    {
        $collect = collect([1, 2, 3]);
        $this->assertEqualsCanonicalizing([1, 2, 3], $collect->all());
    }
    public function testForEach(): void
    {
        $col = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        foreach ($col as $key => $value) {
            $this->assertEquals($key + 1, $value);
        }
    }
    public function testPushPop(): void
    {
        $col = collect([]);
        $col->push(1, 2, 14);
        assertEqualsCanonicalizing([1, 2, 14], $col->all());

        $result = $col->pop();
        assertEquals(14, $result);
        assertEqualsCanonicalizing([1, 2], $col->all());
    }

    public function testMap()
    {
        $col = collect([22, 21, 27]);
        $result = $col->map(fn ($item) => $item * 2);

        assertEqualsCanonicalizing([44, 42, 54], $result->all());
    }
    public function testMapInto()
    {
        $col = collect(["Sven"]);
        $result = $col->mapInto(Person::class);

        assertEquals([new Person("Sven")], $result->all());
    }

    public function testMapSpread()
    {
        $collection = collect(
            [
                ["Eko", "Kunthadi"],
                ["Edwin", "Kurniawan"]
            ]
        );
        $result = $collection->mapSpread(function ($firstName, $lastName) {
            $fullName = $firstName . ' ' . $lastName;
            return new Person($fullName);
        });

        assertEquals([
            new Person("Eko Kunthadi"),
            new Person("Edwin Kurniawan")
        ], $result->all());
    }

    public function testMapToGroups()
    {
        $collect = collect([
            [
                "name" => "Eko",
                "division" => "Organisation"
            ],
            [
                "name" => "Mardhani",
                "division" => "Organisation"
            ],
            [
                "name" => "Robert",
                "division" => "Advocate"
            ]
        ]);

        $result = $collect->mapToGroups(function ($person) {
            return [
                $person["division"] => $person["name"]
            ];
        });

        assertEquals([
            "Organisation" => collect(["Eko", "Mardhani"]),
            "Advocate" => collect(["Robert"])
        ], $result->all());
    }

    public function testZip()
    {
        $collect1 = collect([1, 2, 3]);
        $collect2 = collect([4, 5, 6]);
        $colzip = $collect1->zip($collect2);

        assertEquals([
            collect([1, 4]),
            collect([2, 5]),
            collect([3, 6])
        ], $colzip->all());
    }
    public function testConcat()
    {
        $collect1 = collect([1, 2, 3]);
        $collect2 = collect([4, 5, 6]);
        $collect3 = $collect1->concat($collect2);

        assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6], $collect3->all());
    }
    public function testCombine()
    {
        $collect1 = collect(["name", "car"]);
        $collect2 = collect(["Eko", "Xenia"]);
        $collect3 = $collect1->combine($collect2);

        assertEqualsCanonicalizing([
            "name" => "Eko",
            "car" => "Xenia"
        ], $collect3->all());
    }
    public function testCollapse()
    {
        $col = collect([
            [3, 4, 5],
            [6, 7, 8],
            [9, 10, 11]
        ]);
        $result = $col->collapse();
        assertEqualsCanonicalizing(
            [3, 4, 5, 6, 7, 8, 9, 10, 11],
            $result->all()
        );
    }

    public function testFlapMap(): void
    {
        $collection = collect(
            [
                [
                    "name" => "Edwin",
                    "courses" => ["Next.js", "PHP"]
                ],
                [
                    "name" => "Vicky",
                    "courses" => ["ReactN", "Golang"]
                ],
            ]
        );

        $result = $collection->flatMap(function ($item) {
            $courses = $item["courses"];
            return $courses;
        });
        assertEqualsCanonicalizing(["Next.js", "PHP", "ReactN", "Golang"], $result->all());
    }
    public function testStringRepresentation()
    {
        $collection = collect(["Vicky", "Alexander", "Susanto"]);

        assertEquals("Vicky-Alexander-Susanto", $collection->join("-"));
        assertEquals("Vicky-Alexander_Susanto", $collection->join("-", "_"));
        assertEquals("Vicky, Alexander and Susanto", $collection->join(", ", " and "));
    }
    public function testFilter(): void
    {
        $collect = collect(
            [
                "Budi" => 100,
                "Eko" => 65,
                "Rudi" => 80,
                "Joko" => 90
            ]
        );
        $result = $collect->filter(fn ($value, $key) => $value >= 90);
        assertEquals([
            "Budi" => 100,
            "Joko" => 90
        ], $result->all());
    }
    public function testFilterIndex()
    {
        $collect = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $result = $collect->filter(fn ($value, $key) => $value % 2 == 0);

        assertEqualsCanonicalizing([2, 4, 6, 8, 10], $result->all());
    }

    public function testPartition()
    {
        $collection = collect([
            "Budi" => 100,
            "Eko" => 65,
            "Rudi" => 80,
            "Joko" => 90
        ]);

        [$result1, $result2] = $collection->partition(fn ($value, $key) => $value >= 90);

        assertEquals([
            "Budi" => 100,
            "Joko" => 90
        ], $result1->all());
        assertEquals([
            "Eko" => 65,
            "Rudi" => 80
        ], $result2->all());
    }

    public function testTesting(): void
    {
        $collect = collect([11, "Taylor Otwell", 12, "Rachel"]);
        $data = collect(
            [
                "id" => 12,
                "name" => "Rachel"
            ]
        );

        assertTrue($collect->contains("Rachel"));
        assertTrue($data->has("id"));
        assertTrue($collect->contains(fn ($value, $key) => $value == 11));
    }
    public function testGrouping(){
        $collect = collect([
            [
                "name" => "Eko",
                "dept" => "IT"
            ],
            [
                "name" => "Mardhani",
                "dept" => "IT"
            ],
            [
                "name" => "Robert",
                "dept" => "HR"
            ]
        ]);
        $result = $collect->groupBy("dept");
        assertEquals([
            "IT" => collect([
                [
                    "name" => "Eko",
                    "dept" => "IT"
                ],
                [
                    "name" => "Mardhani",
                    "dept" => "IT"
                ]
            ]),
            "HR" => collect([
                [
                    "name" => "Robert",
                    "dept" => "HR"
                ]
            ])
        ], $result->all());

        $result = $collect->groupBy(fn($value,$key)=>strtolower($value["dept"]));

        assertEquals([
            "it" => collect([
                [
                    "name" => "Eko",
                    "dept" => "IT"
                ],
                [
                    "name" => "Mardhani",
                    "dept" => "IT"
                ]
            ]),
            "hr" => collect([
                [
                    "name" => "Robert",
                    "dept" => "HR"
                ]
            ])
        ], $result->all());
    }
}
