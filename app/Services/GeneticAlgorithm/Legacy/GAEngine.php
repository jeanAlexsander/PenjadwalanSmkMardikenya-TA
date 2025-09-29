<?php

namespace App\Services\GeneticAlgorithm\Legacy;

class GAEngine
{
    /** @var array<string> */
    private array $kelas = ['10 Kuliner', '10 Busana', '11 Kuliner', '11 Busana', '12 Kuliner', '12 Busana'];

    /** @var array<string> */
    private array $hari  = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    public function setKelas(array $kelas): void
    {
        $this->kelas = $kelas;
    }
    public function setHari(array $hari): void
    {
        $this->hari  = $hari;
    }

    // ===== Helper jam & slot =====
    private function getJamPerHari(string $hari): int
    {
        return $hari === 'Jumat' ? 10 : 12;
    }

    private function getJamIstirahat(): array
    {
        return [4, 8];
    }

    private function getJamEkskul(string $hari): array
    {
        return match ($hari) {
            'Rabu'  => [10, 11, 12],
            'Jumat' => [9, 10],
            default => []
        };
    }

    private function isJamNonPelajaran(int $jam, string $hari): bool
    {
        return in_array($jam, $this->getJamIstirahat(), true)
            || in_array($jam, $this->getJamEkskul($hari), true);
    }

    private function generateSlotKosong(string $hari): array
    {
        $slots = [];
        $jumlah = $this->getJamPerHari($hari);
        for ($i = 1; $i <= $jumlah; $i++) {
            if (!$this->isJamNonPelajaran($i, $hari)) $slots[] = $i;
        }
        return $slots;
    }

    private function getKegiatanJamNol(string $hari): string
    {
        return [
            'Senin' => 'Upacara',
            'Selasa' => 'Senam',
            'Rabu'  => 'Literasi Agama',
            'Kamis' => 'Literasi Umum',
            'Jumat' => 'Senam & Kebersihan',
        ][$hari] ?? '-';
    }


    // ===== Core pembuatan jadwal awal =====
    private function generateJadwal(array $kelasList, array $mapelList, array $hariList): array
    {
        $jadwal = [];
        $slotGuru = [];
        $perHari = [];
        $sisaMapel = [];

        foreach ($kelasList as $kelas) {
            foreach ($hariList as $hari) {
                $perHari[$kelas][$hari] = [];

                foreach ($this->getJamIstirahat() as $jam) {
                    $jadwal[] = ['kelas' => $kelas, 'hari' => $hari, 'jam' => $jam, 'mapel' => 'ISTIRAHAT', 'guru' => '-'];
                    $perHari[$kelas][$hari][$jam] = true;
                }
                foreach ($this->getJamEkskul($hari) as $jam) {
                    $jadwal[] = ['kelas' => $kelas, 'hari' => $hari, 'jam' => $jam, 'mapel' => 'EKSKUL', 'guru' => 'Pembina'];
                    $perHari[$kelas][$hari][$jam] = true;
                }
            }
        }

        foreach ($kelasList as $kelas) {
            if (!isset($mapelList[$kelas])) continue;

            $mapelKelas = $mapelList[$kelas];
            shuffle($mapelKelas);

            // prioritaskan praktik
            usort($mapelKelas, function ($a, $b) {
                $isA = !empty($a['is_praktik']) || in_array($a['nama'], ['Dasar2 Keahlian', 'Kuliner', 'Busana', 'PKK', 'Informatika', 'DKV'], true);
                $isB = !empty($b['is_praktik']) || in_array($b['nama'], ['Dasar2 Keahlian', 'Kuliner', 'Busana', 'PKK', 'Informatika', 'DKV'], true);
                if ($isA !== $isB) return $isB <=> $isA;      // praktik dulu
                return ($b['jam'] ?? 0) <=> ($a['jam'] ?? 0); // lalu yang jamnya lebih besar dulu
            });
            foreach ($mapelKelas as $m) {
                $jamTersisa = (int)$m['jam'];
                $guru  = $m['guru'];
                $mapel = $m['nama'];
                $isPraktik = !empty($m['is_praktik']) || in_array($mapel, ['Dasar2 Keahlian', 'Kuliner', 'Busana', 'PKK', 'Informatika', 'DKV'], true);
                $blok = [];

                if ($isPraktik) {
                    $blok = ($jamTersisa > 10) ? [ceil($jamTersisa / 2), floor($jamTersisa / 2)] : [$jamTersisa];
                } else {
                    if (in_array($jamTersisa, [2, 3], true))      $blok = [$jamTersisa];
                    elseif ($jamTersisa === 4) $blok = [2, 2];
                    elseif ($jamTersisa === 5) $blok = [3, 2];
                    elseif ($jamTersisa === 6) $blok = [3, 3];
                    elseif ($jamTersisa === 7) $blok = [3, 2, 2];
                    elseif ($jamTersisa === 8) $blok = [3, 3, 2];
                    else {
                        while ($jamTersisa > 0) {
                            $bagian = ($jamTersisa >= 3) ? 3 : $jamTersisa;
                            $blok[] = $bagian;
                            $jamTersisa -= $bagian;
                        }
                    }
                }

                foreach ($blok as $durasi) {
                    $dijadwalkan = false;

                    if ($isPraktik) {
                        $hariOrder = $hariList;
                        shuffle($hariOrder);
                        foreach ($hariOrder as $hari) {
                            $slots = $this->generateSlotKosong($hari);
                            if (count($slots) < $durasi) continue;

                            $startIdxList = range(0, count($slots) - $durasi);
                            shuffle($startIdxList);

                            foreach ($startIdxList as $si) {
                                $range = array_slice($slots, $si, $durasi);
                                $bentrok = false;
                                foreach ($range as $jam) {
                                    if (isset($perHari[$kelas][$hari][$jam]) || isset($slotGuru[$guru][$hari][$jam])) {
                                        $bentrok = true;
                                        break;
                                    }
                                }
                                if (!$bentrok) {
                                    foreach ($range as $jam) {
                                        $jadwal[] = ['kelas' => $kelas, 'hari' => $hari, 'jam' => $jam, 'mapel' => $mapel, 'guru' => $guru];
                                        $perHari[$kelas][$hari][$jam] = true;
                                        $slotGuru[$guru][$hari][$jam] = true;
                                    }
                                    $dijadwalkan = true;
                                    break 2;
                                }
                            }
                        }
                    } else {
                        foreach ($hariList as $hari) {
                            $slots = $this->generateSlotKosong($hari);
                            for ($i = 0; $i <= count($slots) - $durasi; $i++) {
                                $range = array_slice($slots, $i, $durasi);
                                $bentrok = false;
                                foreach ($range as $jam) {
                                    if (isset($perHari[$kelas][$hari][$jam]) || isset($slotGuru[$guru][$hari][$jam])) {
                                        $bentrok = true;
                                        break;
                                    }
                                }
                                if (!$bentrok) {
                                    foreach ($range as $jam) {
                                        $jadwal[] = ['kelas' => $kelas, 'hari' => $hari, 'jam' => $jam, 'mapel' => $mapel, 'guru' => $guru];
                                        $perHari[$kelas][$hari][$jam] = true;
                                        $slotGuru[$guru][$hari][$jam] = true;
                                    }
                                    $dijadwalkan = true;
                                    break 2;
                                }
                            }
                        }
                    }

                    if (!$dijadwalkan) {
                        $sisaMapel[$kelas][$mapel] = ($sisaMapel[$kelas][$mapel] ?? 0) + $durasi;
                    }
                }
            }
        }

        return ['jadwal' => $jadwal, 'sisa' => $sisaMapel];
    }

    // ===== Perhitungan & diagnosa =====
    private function hitungSisaJam(array $jadwal, array $mapelList): array
    {
        $terhitung = [];
        foreach ($jadwal as $j) {
            if ($this->isJamNonPelajaran((int)$j['jam'], (string)$j['hari'])) continue;
            $terhitung[$j['kelas']][$j['mapel']][$j['guru'] ?? '-'] =
                ($terhitung[$j['kelas']][$j['mapel']][$j['guru'] ?? '-'] ?? 0) + 1;
        }

        $sisa = [];
        foreach ($mapelList as $kelas => $daftar) {
            foreach ($daftar as $m) {
                $mapel  = $m['nama'];
                $guru   = $m['guru'];
                $target = (int)$m['jam'];
                $sudah  = $terhitung[$kelas][$mapel][$guru] ?? 0;
                $kurang = max(0, $target - $sudah);
                if ($kurang > 0) $sisa[$kelas]["{$mapel}|{$guru}"] = $kurang;
            }
        }
        return $sisa;
    }

    private function penaltiGapSoft(array $perKelasHari): int
    {
        $p = 0;
        foreach ($perKelasHari as $kelas => $hariJam) {
            foreach ($hariJam as $hari => $jamTerisi) {
                $total = $this->getJamPerHari($hari);
                $isi = [];
                for ($j = 1; $j <= $total; $j++) {
                    if (isset($jamTerisi[$j]) && !in_array($jamTerisi[$j], ['ISTIRAHAT', 'EKSKUL'], true)) $isi[] = $j;
                }
                if (count($isi) < 2) continue;

                for ($k = 0; $k < count($isi) - 1; $k++) {
                    $a = $isi[$k];
                    $b = $isi[$k + 1];
                    if ($b - $a <= 1) continue;
                    for ($t = $a + 1; $t <= $b - 1; $t++) {
                        if (!$this->isJamNonPelajaran($t, $hari) && !isset($jamTerisi[$t])) $p++;
                    }
                }
            }
        }
        return $p;
    }

    private function hitungFitness(array $jadwalBundle, array $mapelList, float $alpha, float $beta): float
    {
        $jadwal = $jadwalBundle['jadwal'] ?? [];
        $sisa   = $this->hitungSisaJam($jadwal, $mapelList);

        $hardPenalty = 0;
        $softPenalty = 0;

        $guruSlot = [];
        $perKelasHari = [];

        foreach ($jadwal as $j) {
            $perKelasHari[$j['kelas']][$j['hari']][$j['jam']] = $j['mapel'];

            if (!in_array($j['mapel'], ['ISTIRAHAT', 'EKSKUL'], true)) {
                $key = $j['guru'] . '-' . $j['hari'] . '-' . $j['jam'];
                if (isset($guruSlot[$key])) $hardPenalty += 1;
                else $guruSlot[$key] = true;
            }

            if (in_array($j['jam'], $this->getJamIstirahat(), true) && $j['mapel'] !== 'ISTIRAHAT') $hardPenalty += 1;
            if (in_array($j['jam'], $this->getJamEkskul($j['hari']), true) && $j['mapel'] !== 'EKSKUL') $hardPenalty += 1;
        }

        foreach ($sisa as $mapelSisa) foreach ($mapelSisa as $kurang) $hardPenalty += $kurang;

        $softPenalty += $this->penaltiGapSoft($perKelasHari);
        // $softPenalty += $this->hitungSlotKosongSoft($perKelasHari); // opsional

        return 1.0 / (1.0 + $alpha * $hardPenalty + $beta * $softPenalty);
    }

    private function diagnosaKomponen(array $jadwalBundle, array $mapelList, float $alpha, float $beta): array
    {
        $jadwal = $jadwalBundle['jadwal'] ?? [];
        $sisa   = $this->hitungSisaJam($jadwal, $mapelList);

        $hard = 0;
        $soft = 0;
        $guruSlot = [];
        $perKelasHari = [];
        $conflicts = [];

        foreach ($jadwal as $j) {
            $perKelasHari[$j['kelas']][$j['hari']][$j['jam']] = $j['mapel'];

            if (!in_array($j['mapel'], ['ISTIRAHAT', 'EKSKUL'], true)) {
                $key = $j['guru'] . '-' . $j['hari'] . '-' . $j['jam'];
                if (isset($guruSlot[$key])) {
                    $hard += 1;
                    $conflicts[] = "Bentrok guru {$j['guru']} @ {$j['hari']} jam {$j['jam']} (kelas {$j['kelas']})";
                } else $guruSlot[$key] = true;
            }

            if (in_array($j['jam'], $this->getJamIstirahat(), true) && $j['mapel'] !== 'ISTIRAHAT') $hard += 1;
            if (in_array($j['jam'], $this->getJamEkskul($j['hari']), true) && $j['mapel'] !== 'EKSKUL')   $hard += 1;
        }

        foreach ($sisa as $mapelSisa) foreach ($mapelSisa as $kurang) $hard += $kurang;

        $soft += $this->penaltiGapSoft($perKelasHari);
        $fitness = 1.0 / (1.0 + $alpha * $hard + $beta * $soft);

        return ['fitness' => $fitness, 'hard' => $hard, 'soft' => $soft, 'conflicts' => $conflicts];
    }

    // ===== Operator GA =====
    private function crossover(array $parentA, array $parentB): array
    {
        $jadwalA = $parentA['jadwal'];
        $jadwalB = $parentB['jadwal'];

        $kelasSet = array_values(array_unique(array_merge(
            array_column($jadwalA, 'kelas'),
            array_column($jadwalB, 'kelas')
        )));
        shuffle($kelasSet);
        $cut = max(1, (int) floor(count($kelasSet) / 2));

        $kelasA = array_slice($kelasSet, 0, $cut);
        $kelasB = array_slice($kelasSet, $cut);

        $childJadwal = array_filter($jadwalA, fn($j) => in_array($j['kelas'], $kelasA, true));
        $childJadwal = array_merge($childJadwal, array_filter($jadwalB, fn($j) => in_array($j['kelas'], $kelasB, true)));

        return ['jadwal' => array_values($childJadwal)];
    }

    private function mutasi(array &$individu, float $prob = 0.2): void
    {
        if (mt_rand() / mt_getrandmax() > $prob) return;

        $jadwal = &$individu['jadwal'];
        if (count($jadwal) < 2) return;

        $idx1 = array_rand($jadwal);
        $found = false;
        $idx2 = null;
        $tries = 0;

        while ($tries++ < 30) {
            $cand = array_rand($jadwal);
            if ($cand === $idx1) continue;
            if (
                $jadwal[$cand]['kelas'] === $jadwal[$idx1]['kelas'] &&
                $jadwal[$cand]['hari']  === $jadwal[$idx1]['hari']
            ) {
                $idx2 = $cand;
                $found = true;
                break;
            }
        }
        if (!$found) return;

        if (in_array($jadwal[$idx1]['mapel'], ['ISTIRAHAT', 'EKSKUL'], true)) return;
        if (in_array($jadwal[$idx2]['mapel'], ['ISTIRAHAT', 'EKSKUL'], true)) return;

        [$jadwal[$idx1], $jadwal[$idx2]] = [$jadwal[$idx2], $jadwal[$idx1]];
    }

    private function seleksiTurnamen(array $pop, int $k = 3): array
    {
        $k = max(2, $k);
        $cand = [];
        for ($i = 0; $i < $k; $i++) $cand[] = $pop[array_rand($pop)];
        usort($cand, fn($a, $b) => $b['fitness'] <=> $a['fitness']);
        return ['jadwal' => $cand[0]['jadwal'], 'fitness' => $cand[0]['fitness']];
    }

    // ===== Repair =====
    private function repairJadwal(array $jadwalAwal, array $mapelList, array $kelasList, array $hariList): array
    {
        $jadwal = $jadwalAwal;
        $perKelasHari = [];
        $slotGuru = [];

        foreach ($jadwal as $j) {
            $perKelasHari[$j['kelas']][$j['hari']][$j['jam']] = true;
            $slotGuru[$j['guru']][$j['hari']][$j['jam']] = true;
        }

        foreach ($kelasList as $kelas) {
            if (!isset($mapelList[$kelas])) continue;
            foreach ($mapelList[$kelas] as $m) {
                $mapel = $m['nama'];
                $guru = $m['guru'];
                $jamTotal = (int)$m['jam'];

                $sudah = 0;
                foreach ($jadwal as $j) if ($j['kelas'] === $kelas && $j['mapel'] === $mapel) $sudah++;

                $kurang = $jamTotal - $sudah;
                if ($kurang <= 0) continue;

                foreach ($hariList as $hari) {
                    $slots = $this->generateSlotKosong($hari);
                    foreach ($slots as $jam) {
                        if ($kurang <= 0) break;
                        if (isset($perKelasHari[$kelas][$hari][$jam]) || isset($slotGuru[$guru][$hari][$jam])) continue;

                        $jadwal[] = ['kelas' => $kelas, 'hari' => $hari, 'jam' => $jam, 'mapel' => $mapel, 'guru' => $guru];
                        $perKelasHari[$kelas][$hari][$jam] = true;
                        $slotGuru[$guru][$hari][$jam] = true;
                        $kurang--;
                    }
                }
            }
        }

        return $jadwal;
    }

    private function repairSisaJamGreedy(array $jadwal, array $mapelList, array $kelasList, array $hariList): array
    {
        $byKHJ = [];
        $byGHJ = [];
        foreach ($jadwal as $i => $j) {
            $byKHJ[$j['kelas']][$j['hari']][$j['jam']] = $i;
            if (!in_array($j['mapel'], ['ISTIRAHAT', 'EKSKUL'], true))
                $byGHJ[$j['guru']][$j['hari']][$j['jam']] = $i;
        }

        foreach ($kelasList as $kelas) {
            if (!isset($mapelList[$kelas])) continue;

            $terisi = [];
            foreach ($jadwal as $x) {
                if ($x['kelas'] === $kelas && !in_array($x['mapel'], ['ISTIRAHAT', 'EKSKUL'], true)) {
                    $terisi[$x['mapel']] = ($terisi[$x['mapel']] ?? 0) + 1;
                }
            }

            foreach ($mapelList[$kelas] as $m) {
                $nama = $m['nama'];
                $guru = $m['guru'];
                $target = (int)$m['jam'];
                $kurang = $target - ($terisi[$nama] ?? 0);
                if ($kurang <= 0) continue;

                // 1) isi slot kosong aman
                foreach ($hariList as $h) {
                    if ($kurang <= 0) break;
                    $slots = $this->generateSlotKosong($h);
                    foreach ($slots as $jam) {
                        if ($kurang <= 0) break;
                        if (isset($byKHJ[$kelas][$h][$jam])) continue;
                        if (isset($byGHJ[$guru][$h][$jam]))  continue;

                        $jadwal[] = ['kelas' => $kelas, 'hari' => $h, 'jam' => $jam, 'mapel' => $nama, 'guru' => $guru];
                        $idx = array_key_last($jadwal);
                        $byKHJ[$kelas][$h][$jam] = $idx;
                        $byGHJ[$guru][$h][$jam]  = $idx;
                        $terisi[$nama] = ($terisi[$nama] ?? 0) + 1;
                        $kurang--;
                    }
                }
                if ($kurang <= 0) continue;

                // 2) geser mapel lain untuk membuka slot
                foreach ($hariList as $h) {
                    if ($kurang <= 0) break;
                    $slots = $this->generateSlotKosong($h);
                    foreach ($slots as $jam) {
                        if ($kurang <= 0) break;
                        if (isset($byGHJ[$guru][$h][$jam])) continue;

                        if (!isset($byKHJ[$kelas][$h][$jam])) continue;
                        $idxLain = $byKHJ[$kelas][$h][$jam];
                        $pelLain = $jadwal[$idxLain];
                        if (in_array($pelLain['mapel'], ['ISTIRAHAT', 'EKSKUL'], true)) continue;

                        $relocate = null;
                        foreach ($hariList as $h2) {
                            $slots2 = $this->generateSlotKosong($h2);
                            shuffle($slots2);
                            foreach ($slots2 as $jam2) {
                                if (isset($byKHJ[$kelas][$h2][$jam2])) continue;
                                if (
                                    !in_array($pelLain['mapel'], ['ISTIRAHAT', 'EKSKUL'], true) &&
                                    isset($byGHJ[$pelLain['guru']][$h2][$jam2])
                                ) continue;
                                $relocate = [$h2, $jam2];
                                break 2;
                            }
                        }
                        if (!$relocate) continue;

                        unset($byKHJ[$kelas][$h][$jam]);
                        if (!in_array($pelLain['mapel'], ['ISTIRAHAT', 'EKSKUL'], true))
                            unset($byGHJ[$pelLain['guru']][$h][$jam]);

                        [$h2, $jam2] = $relocate;
                        $jadwal[$idxLain]['hari'] = $h2;
                        $jadwal[$idxLain]['jam']  = $jam2;
                        $byKHJ[$kelas][$h2][$jam2] = $idxLain;
                        if (!in_array($pelLain['mapel'], ['ISTIRAHAT', 'EKSKUL'], true))
                            $byGHJ[$pelLain['guru']][$h2][$jam2] = $idxLain;

                        $jadwal[] = ['kelas' => $kelas, 'hari' => $h, 'jam' => $jam, 'mapel' => $nama, 'guru' => $guru];
                        $idxBaru = array_key_last($jadwal);
                        $byKHJ[$kelas][$h][$jam] = $idxBaru;
                        $byGHJ[$guru][$h][$jam]  = $idxBaru;

                        $terisi[$nama] = ($terisi[$nama] ?? 0) + 1;
                        $kurang--;
                    }
                }
            }
        }
        return $jadwal;
    }

    // ===== PUBLIC API =====
    public function run(array $mapel, array $ops = []): array
    {
        $kelas = $this->kelas;
        $hari  = $this->hari;

        $populasiSize = (int)($ops['population']  ?? 50);
        $generasiMax  = (int)($ops['generations'] ?? 50);
        $elitSize     = (int)($ops['elit']        ?? 2);
        $pCrossover   = (float)($ops['pCrossover'] ?? 0.9);
        $pMutasi      = (float)($ops['pMutasi']     ?? 0.30);
        $tourSize     = (int)($ops['tourSize']    ?? 3);

        $populasi = [];
        for ($i = 0; $i < $populasiSize; $i++) {
            $bundle  = $this->generateJadwal($kelas, $mapel, $hari);
            $fit     = $this->hitungFitness($bundle, $mapel, 10, 0.20);
            $populasi[] = ['jadwal' => $bundle['jadwal'], 'fitness' => $fit];
        }

        for ($g = 0; $g < $generasiMax; $g++) {
            foreach ($populasi as &$ind) {
                $ind['fitness'] = $this->hitungFitness(['jadwal' => $ind['jadwal']], $mapel, 10, 0.20);
            }
            unset($ind);

            usort($populasi, fn($a, $b) => $b['fitness'] <=> $a['fitness']);
            $baru = array_slice($populasi, 0, $elitSize);

            while (count($baru) < $populasiSize) {
                $p1 = $this->seleksiTurnamen($populasi, $tourSize);
                $p2 = $this->seleksiTurnamen($populasi, $tourSize);

                $child = (mt_rand() / mt_getrandmax() < $pCrossover) ? $this->crossover($p1, $p2)
                    : ['jadwal' => $p1['jadwal']];

                $this->mutasi($child, $pMutasi);
                $child['jadwal']  = $this->repairJadwal($child['jadwal'], $mapel, $kelas, $hari);
                $child['jadwal']  = $this->repairSisaJamGreedy($child['jadwal'], $mapel, $kelas, $hari);
                $child['fitness'] = $this->hitungFitness(['jadwal' => $child['jadwal']], $mapel, 10, 0.20);

                $baru[] = $child;
            }
            $populasi = $baru;
        }

        usort($populasi, fn($a, $b) => $b['fitness'] <=> $a['fitness']);
        $best = $populasi[0]['jadwal'];
        $diag = $this->diagnosaKomponen(['jadwal' => $best], $mapel, 10, 0.20);

        return ['jadwal' => $best, 'diagnosa' => $diag, 'fitness' => $diag['fitness'] ?? $populasi[0]['fitness']];
    }
}
