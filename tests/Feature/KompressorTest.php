<?php

namespace samkitano\Kompressor\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use samkitano\Kompressor\Facades\Kompressor;
use samkitano\Kompressor\Exceptions\KompressorException;

class KompressorTest extends TestCase
{
    /** @test */
    public function throws_error_if_library_not_supported()
    {
        Config::set('kompressor.library', 'bogus'); // TODO: extract to trait

        $this->expectException(KompressorException::class);
        $this->expectExceptionMessage("Library 'Bogus' not supported.");

        Kompressor::compress($this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0]);

        Config::set('kompressor.library', 'zip'); // TODO: extract to trait
    }

    /** @test */
    public function throws_error_if_origin_file_does_not_exist()
    {
        $made_up_file_name = strtolower(str_random().'.'.str_random(3));

        $this->expectException(KompressorException::class);
        $this->expectExceptionMessage("Source '$made_up_file_name' not found.");

        Kompressor::compress($made_up_file_name);
    }

    /** @test */
    public function throws_error_if_origin_dir_does_not_exist()
    {
        $made_up_dir_name = strtolower(str_random());

        $this->expectException(KompressorException::class);
        $this->expectExceptionMessage("Source '$made_up_dir_name' not found.");

        Kompressor::compress($made_up_dir_name);
    }

    /** @test */
    public function throws_error_when_default_archives_directory_is_not_set_and_compress_has_multiple_source_dirs_with_no_destination()
    {
        $default_config = Config::get('default_archives_directory');
        Config::set('default_archives_directory', null);

        $this->expectException(KompressorException::class);
        $this->expectExceptionMessage("Multiple source directories detected. Please see documentation.");

        $files = [
            $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0],
            $this->test_extract_dir.DIRECTORY_SEPARATOR.'extra.txt',
        ];

        Kompressor::compress($files);

        Config::set('default_archives_directory', $default_config);
    }

    /** @test */
    public function throws_error_when_default_archives_directory_is_not_set_and_archiving_multiple_dirs()
    {
        $default_config = Config::get('default_archives_directory');
        Config::set('default_archives_directory', null);

        $this->expectException(KompressorException::class);
        $this->expectExceptionMessage("Sorry, can not archive multiple directories, or mixed sources!");

        $files = [
            $this->test_files_dir,
            $this->test_extract_dir,
        ];

        Kompressor::compress($files);

        Config::set('default_archives_directory', $default_config);
    }

    /** @test */
    public function throws_error_if_mixed_sources()
    {
        $default_config = Config::get('default_archives_directory');
        Config::set('default_archives_directory', null);

        $this->expectException(KompressorException::class);
        $this->expectExceptionMessage("Multiple source directories detected. Please see documentation.");

        $files = [
            $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0],
            $this->test_extract_dir,
        ];

        Kompressor::compress($files);

        Config::set('default_archives_directory', $default_config);
    }

    /** @test */
    public function accepts_an_array_of_files_as_first_argument()
    {
        //$resulting_archive = $this->test_files_dir.DIRECTORY_SEPARATOR.'files'.$this->extension;
        $expected_result = "Created $this->testing_archive_dir with 2 files";
        $compress = [
            $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0],
            $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[1],
        ];

        $result_message = Kompressor::compress($compress);

        $this->assertEquals($expected_result, $result_message);

        unlink($this->testing_archive_dir);
    }

    /** @test */
    public function accepts_a_string_as_first_argument()
    {
        $expected_result = "Created $this->testing_archive_single with 1 file";

        $result_message = Kompressor::compress($this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0]);

        $this->assertEquals($expected_result, $result_message);
        $this->assertTrue(file_exists($this->testing_archive_single));

        // No unlink! Created archive will be used to perform next test.
    }

    /** @test */
    public function can_read_file_names_in_archives()
    {
        $contains_files = Kompressor::read($this->testing_archive_single);
        $expected = (array) $this->test_files[0]; // archived in previous test

        $this->assertEquals($expected, $contains_files);

        unlink($this->testing_archive_single);
    }

    /** @test */
    public function can_archive_a_file_in_same_directory_with_different_name()
    {
        $expected_archive = $this->test_files_dir.DIRECTORY_SEPARATOR.'tested'.$this->extension;
        $compress = $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0];
        $expected_message = "Created $expected_archive with 1 file";

        $actual_message = Kompressor::compress($compress, null, 'tested');

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($expected_archive));

        unlink($expected_archive);
    }

    /** @test */
    public function can_archive_a_file_to_a_given_directory()
    {
        $compress_file = $this->test_files[0];
        $file_name = pathinfo($compress_file, PATHINFO_FILENAME );
        $expected_archive = $this->test_extract_dir.DIRECTORY_SEPARATOR.$file_name.$this->extension;
        $expected_message = "Created $expected_archive with 1 file";

        $actual_message = Kompressor::compress(
            $this->test_files_dir.DIRECTORY_SEPARATOR.$compress_file,
            $this->test_extract_dir
        );

        $this->assertEquals($expected_message, $actual_message);

        unlink($expected_archive);
    }

    /** @test */
    public function can_delete_original_file()
    {
        $compress_file = $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0];
        $expected_archive = $this->testing_archive_single;
        $expected_message = "Created $expected_archive with 1 file";

        $resulting_message = Kompressor::compress(
            $compress_file,
            null,
            null,
            true
        );

        $this->assertEquals($expected_message, $resulting_message);
        $this->assertFalse(file_exists($compress_file), 'Original file was not deleted!');

        // no unlinks here. we will use created archive to perform next test
    }

    /** @test */
    public function can_extract_to_same_directory()
    {
        $this->assertTrue(
            file_exists($this->testing_archive_single),
            "$this->testing_archive_single was not persisted."
        );

        $extracted = $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0];
        $expected_message = "Extracted 1 file from $this->testing_archive_single";

        $actual_message = Kompressor::extract($this->testing_archive_single);

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($extracted));

        // no unlinks here. we will use extracted file to perform next test
    }

    /** @test */
    public function extracted_file_contains_original_info()
    {
        $this->assertTrue(
            file_exists($this->testing_archive_single),
            "$this->testing_archive_single was not persisted."
        );

        $extracted_file = $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0];
        $extracted_content = trim(file_get_contents($extracted_file));

        $this->assertEquals($this->test_files_content, $extracted_content);

        // no unlinks here. we will use existing archive to perform next tests
    }

    /** @test */
    public function can_add_a_file_to_an_existing_archive()
    {
        $this->assertTrue(
            file_exists($this->testing_archive_single),
            "$this->testing_archive_single was not persisted."
        );

        $additional_file = $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[1];
        $expected_message = "1 file added to $this->testing_archive_single";
        $expected_read = [$this->test_files[0], $this->test_files[1]];

        $actual_message = Kompressor::add($this->testing_archive_single, $additional_file);

        $this->assertTrue(file_exists($this->testing_archive_single));
        $this->assertEquals($expected_message, $actual_message);

        $actual_contents = Kompressor::read($this->testing_archive_single);

        $this->assertEquals(
            $expected_read,
            $actual_contents
        );

        // no unlinks here. we will use updated archive to perform next tests
    }

    /** @test */
    public function can_remove_a_file_from_an_existing_archive()
    {
        $this->assertTrue(
            file_exists($this->testing_archive_single),
            "Can not perform test. $this->testing_archive_single was not persisted."
        );

        $expected_message = "1 file removed from $this->testing_archive_single";

        $actual_message = Kompressor::remove($this->testing_archive_single, $this->test_files[0]);

        $this->assertTrue(file_exists($this->testing_archive_single));
        $this->assertEquals($expected_message, $actual_message);

        $actual_contents = Kompressor::read($this->testing_archive_single);

        $this->assertEquals(
            [$this->test_files[1]],
            $actual_contents
        );

        // no unlinks here. we will use updated archive to perform next test
    }

    /** @test */
    public function can_add_multiple_files_to_an_existing_archive()
    {
        $this->assertTrue(
            file_exists($this->testing_archive_single),
            "Can not perform test. $this->testing_archive_single was not persisted."
        );

        $expected_message = "2 files added to $this->testing_archive_single";
        $result = [$this->test_files[0], $this->test_files[1], $this->test_files[2]];
        $additional_files = [
            $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[0],
            $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[2],
        ];

        $actual_message = Kompressor::add($this->testing_archive_single, $additional_files);

        $this->assertTrue(file_exists($this->testing_archive_single));
        $this->assertEquals($expected_message, $actual_message);

        $actual_contents = Kompressor::read($this->testing_archive_single);

        $diff = array_diff($actual_contents, $result);

        $this->assertEquals(
            $diff,
            []
        );

        // no unlinks here. we will use updated archive to perform next test
    }

    /** @test */
    public function can_extract_multiple_files_from_archive()
    {
        $extract_files = [
            $this->test_files[1],
            $this->test_files[2],
        ];

        $expected_message = "Extracted 2 files from $this->testing_archive_single";

        $actual_message = Kompressor::extract(
            $this->testing_archive_single,
            $this->test_extract_dir,
            false,
            $extract_files
        );

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($this->test_extract_dir.DIRECTORY_SEPARATOR.$this->test_files[1]));
        $this->assertTrue(file_exists($this->test_extract_dir.DIRECTORY_SEPARATOR.$this->test_files[2]));

        // no unlinks here. we will use updated archive to perform next test
    }

    /** @test */
    public function can_remove_multiple_files_from_an_existing_archive()
    {
        $this->assertTrue(
            file_exists($this->testing_archive_single),
            "Can not perform test. $this->testing_archive_single was not persisted."
        );

        $expected_message = "2 files removed from $this->testing_archive_single";
        $files_to_remove = [
            $this->test_files[0],
            $this->test_files[2],
        ];

        $actual_message = Kompressor::remove($this->testing_archive_single, $files_to_remove);

        $this->assertTrue(file_exists($this->testing_archive_single));
        $this->assertEquals($expected_message, $actual_message);

        $actual_contents = Kompressor::read($this->testing_archive_single);

        $this->assertEquals(
            [$this->test_files[1]],
            $actual_contents
        );

        // no unlinks here. we will use updated archive to perform next test
    }

    /** @test */
    public function can_delete_original_archive_after_extraction()
    {
        $this->assertTrue(
            file_exists($this->testing_archive_single),
            "Can not perform test. $this->testing_archive_single was not persisted."
        );

        $expected_message = "Extracted 1 file from $this->testing_archive_single";
        $extracted_file = $this->test_files_dir.DIRECTORY_SEPARATOR.$this->test_files[1];

        $actual_message = Kompressor::extract($this->testing_archive_single, null, true);

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($extracted_file));
        $this->assertFalse(file_exists($this->testing_archive_single)); // archive should not exist anymore
    }

    /** @test */
    public function all_test_files_are_present_by_now()
    {
        foreach ($this->test_files as $test_file) {
            $this->assertTrue(file_exists($this->test_files_dir.DIRECTORY_SEPARATOR.$test_file));
        }
    }

    /** @test */
    public function can_archive_an_entire_directory()
    {
        $expected_archive = $this->testing_archive_dir;
        $n_files = count($this->test_files);
        $word = $n_files === 1 ? 'file' : 'files';
        $expected_message = "Created $expected_archive with $n_files $word";

        $actual_message = Kompressor::compress($this->test_files_dir);

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($expected_archive));

        unlink($expected_archive);
    }

    /** @test */
    public function can_archive_directory_with_different_name()
    {
        $name = 'test';
        $expected_archive = $this->test_files_dir.DIRECTORY_SEPARATOR.$name.$this->extension;
        $n_files = count($this->test_files);
        $word = $n_files === 1 ? 'file' : 'files';
        $expected_message = "Created $expected_archive with $n_files $word";

        $actual_message = Kompressor::compress($this->test_files_dir, null, $name);

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($expected_archive));

        $actual_contents = Kompressor::read($expected_archive);

        $this->assertEquals(
            $this->test_files,
            $actual_contents
        );

        unlink($expected_archive);
    }

    /** @test */
    public function can_archive_directory_with_different_name_and_destination()
    {
        $name = 'test';
        $destination = $this->test_extract_dir;
        $expected_archive = $destination.DIRECTORY_SEPARATOR.$name.$this->extension;
        $n_files = count($this->test_files);
        $word = $n_files === 1 ? 'file' : 'files';
        $expected_message = "Created $expected_archive with $n_files $word";

        $actual_message = Kompressor::compress($this->test_files_dir, $destination, $name);

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($expected_archive));

        $actual_contents = Kompressor::read($expected_archive);

        $this->assertEquals(
            $this->test_files,
            $actual_contents
        );

        unlink($expected_archive);
    }

    /** @test */
    public function can_archive_a_directory_with_a_custom_search_pattern()
    {
        $dir = $this->test_files_dir;
        $pattern = '*.md';
        $files = array_map('basename', glob($this->test_files_dir.DIRECTORY_SEPARATOR.$pattern));
        $n_files = count($files);
        $word = $n_files === 1 ? 'file' : 'files';
        $expected_archive = $dir.DIRECTORY_SEPARATOR.'files'.$this->extension;
        $expected_message = "Created $expected_archive with $n_files $word";

        $actual_message = Kompressor::compress($dir, null, null, false, $pattern);

        $this->assertEquals($expected_message, $actual_message);
        $this->assertTrue(file_exists($expected_archive));

        $archive_content = Kompressor::read($expected_archive);

        $this->assertEquals($files, $archive_content);
    }

    /** @test */
    public function destroy_test_files()
    {
        // clean up test files
        $this->destroyTestFileSystem();

        $this->assertFalse(File::isDirectory($this->test_files_dir));
        $this->assertFalse(File::isDirectory($this->test_extract_dir));
    }
}
