<?php

namespace Tests\Feature;

use App\Data\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;
use function PHPUnit\Framework\assertFalse;
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

    public function testFlatMap(): void
    {
        $collection = collect(
            [
                [
                    "name" => "Edwin",
                    "courses" => ["Next.js", "PHP"]
                ],
                [
                    "name" => "Vicky",
                    "courses" => ["React Native", "Golang"]
                ],
            ]
        );

        $result = $collection->flatMap(function ($item) {
            $courses = $item["courses"];
            return $courses;
        });
        assertEqualsCanonicalizing(["Next.js", "PHP", "React Native", "Golang"], $result->all());
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
    public function testGrouping()
    {
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

        $result = $collect->groupBy(fn ($value, $key) => strtolower($value["dept"]));

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
    public function testSlice()
    {
        $collect =  collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $res = $collect->slice(4);
        assertEqualsCanonicalizing([5, 6, 7, 8, 9], $res->all());

        $res = $collect->slice(3, 3);
        assertEqualsCanonicalizing([4, 5, 6], $res->all());
    }
    public function testTake()
    {
        $collect = collect([1, 2, 3, 1, 2, 3, 1, 2, 3]);
        $result = $collect->take(3);
        assertEqualsCanonicalizing([1, 2, 3], $result->all());

        $result = $collect->takeUntil(fn ($value, $key) => $value == 3);
        assertEqualsCanonicalizing([1, 2], $result->all());
        $result = $collect->takeWhile(fn ($value, $key) => $value < 3);
        assertEqualsCanonicalizing([1, 2], $result->all());
    }

    public function testSkip()
    {
        $collect =  collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collect->skip(3);
        $this->assertEqualsCanonicalizing([4, 5, 6, 7, 8, 9], $result->all());

        $result = $collect->skipUntil(fn ($value) => $value == 3);
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());

        $result = $collect->skipWhile(fn ($value) => $value < 3);
        $this->assertEqualsCanonicalizing([3, 4, 5, 6, 7, 8, 9], $result->all());
    }
    public function testChunk()
    {
        $collect = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $result = $collect->chunk(3);

        $this->assertEqualsCanonicalizing([1, 2, 3], $result->all()[0]->all());
        $this->assertEqualsCanonicalizing([4, 5, 6], $result->all()[1]->all());
        $this->assertEqualsCanonicalizing([7, 8, 9], $result->all()[2]->all());
        $this->assertEqualsCanonicalizing([10], $result->all()[3]->all());
    }
    public function testFirst()
    {
        $collect = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collect->first();
        assertEquals(1, $result);

        $result = $collect->first(
            fn ($value, $key) => $value > 5
        );
        assertEquals(6, $result);
    }
    public function testLast()
    {

        $collect = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $result = $collect->last();
        assertEquals(9, $result);

        $result = $collect->last(function ($value, $key) {
            return $value < 5;
        });
        assertEquals(4, $result);
    }
    public function testColRandom()
    {
        $col = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $res = $col->random();
        assertTrue(in_array($res, [1, 2, 3, 4, 5, 6, 7, 8, 9]));
    }
    public function testCheckExist()
    {
        $col = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        assertTrue($col->isNotEmpty());
        assertFalse($col->isEmpty());
        assertTrue($col->contains(3));
        assertTrue($col->contains(fn ($value) => $value == 4));
    }
    public function testOrder()
    {
        $col = collect([11, 13, 1, 2, 3, 4, 10, 6, 5, 8, 7, 9]);
        $result = $col->sort();
        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 13], $result->all());

        $result = $col->sortDesc();
        $this->assertEqualsCanonicalizing([13, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1], $result->all());
    }

    public function testAggregate()
    {
        $col = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $res = $col->sum();
        assertEquals(45, $res);
        $res = $col->avg();
        assertEquals(5, $res);
        $res = $col->max();
        assertEquals(9, $res);
    }
    public function testReduce()
    {
        $col = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $res = $col->reduce(fn($carry,$item)=>$carry+$item);
        assertEquals(45,$res);
    }
}
