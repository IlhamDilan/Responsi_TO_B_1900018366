<?php

class parameters{
    const file_name = 'D:\Laragon\www\Tesi\Praktikum\1\produk.txt';
    const columns = ['item', 'price'];
    const population = 30;
    const budget = 280000;
    const STOPPING_VALUE = 20000;
    const CROSSOVER_RATE = 0.8;
}

class cataloge
{
    function createProductColumn($listOfRawProduct){
        foreach(array_keys($listOfRawProduct) as $listOfRawProductKey){
            $listOfRawProduct[parameters::columns[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
    }

    function product(){
        $collectionOfListProduct = [];

        $raw_data = file(parameters::file_name);
        foreach($raw_data as $listOfRawProduct){
            $collectionOfListProduct[] = $this->createProductColumn(explode(",",$listOfRawProduct));    
        }
        
        return $collectionOfListProduct;
    }
}

class individu{
    function countnumberofgen(){
        $c = new cataloge;
        return count($c->product());
    }

    function createrandomindividu(){ 
        //echo $this->countnumberofgen();
        for ($i=0;$i<$this->countnumberofgen();$i++){
            $ret[] = rand(0,1);
        }
        return $ret;
    }
}

class population{

    function createrandompopulation(){
        $ind = new individu;
        for($i=0;$i<parameters::population;$i++){
            $ret[]=$ind->createrandomindividu();
        }
        return $ret;
    }
}

class fitnes{
    function selectingitem($individu){
        $ca = new cataloge;
        foreach($individu as $individukey=>$binaryGen){
            // print_r($individukey);
            // echo '<br>';
            // print_r($binaryGen);
            if($binaryGen == 1){
                $ret[] = [
                    'selectedKey' => $individukey,
                    'selectedPrice' => $ca->product()[$individukey]['price']
                ];    
            }
        }
        //exit();
        return $ret;
        
    }

    function calculateFitnessValue($individu){
        return array_sum(array_column($this->selectingitem($individu), 'selectedPrice'));
        //print_r($this->selectingitem($individu));
        //exit();        
    }

    function isFit($fitnessValue){
        if($fitnessValue<=parameters::budget){
            return True;
        }
    }

    function countSelectedItems($individu){
        return count($this->selectingitem($individu));
    }

    function searchBestIndividu($fits, $maxItem, $numberOfIndividuMaxItem){
        if($numberOfIndividuMaxItem==1){
            $index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
            return $fits[$index];
            // $out = $fits[$index];
            // print_r('Nilainya: ');
            // print_r($out);
        }else{
            foreach($fits as $key => $value){
                if($value['numberOfSelectedItem']==$maxItem){
                    echo $key.' '.$value['fitnessValue'].'<br>';
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $value['fitnessValue']
                    ];
                }
            }
            if(count(array_unique(array_column($ret, 'fitnessValue')))==1){
                $index = rand(0, count($ret)-1);
            } else{
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }
            //echo 'Hasilnya: ';
            //echo 'Best Fitness Value Jika Memiliki Lebih dari 1 Individu dan Terbaik:';    
            return $ret[$index];
        }
    }

    function isFound($fits){
        $countedMaxItems= array_count_values(array_column($fits, 'numberOfSelectedItem'));
        //print_r($countedMaxItems);
        //echo '<br>';
        $maxItem = max(array_keys($countedMaxItems));
        // echo 'Item Maksimal: '.$maxItem;
        // echo '<br>';
        $numberOfIndividuMaxItem = $countedMaxItems[$maxItem];
        echo 'Jumlah Individu: '.$numberOfIndividuMaxItem;
        echo '<br>';
        
        $besFitnesValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuMaxItem)['fitnessValue'];
        echo '<br> Best Fitness Value: '.$besFitnesValue;

        $residual = parameters::budget - $besFitnesValue;
        echo '<br> Residual: '.$residual;

        if($residual <= parameters::STOPPING_VALUE && $residual >0){
            return True;
        }
    }

    function fitnessevaluation($population){
        $ca = new cataloge;
        foreach($population as $listofindividukey=>$listofindividu){
            echo 'Individu- '. $listofindividukey. '<br>';
            foreach($listofindividu as $individukey=>$binaryGen){
                //echo $binaryGen.'&nbsp;&nbsp;';
                // print_r($ca->product()[$individukey]);
                //echo '<br>';
            }
            $fitnessValue = $this->calculateFitnessValue($listofindividu);
            $numofselecteditem = $this->countselecteditems($listofindividu);
            echo 'Max. Item: '. $numofselecteditem;
            echo '<br>';
            echo 'Fitness Value: '. $fitnessValue;
            if($this->isFit($fitnessValue)){
                echo ' (Fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listofindividukey,        
                    'numberOfSelectedItem' => $numofselecteditem, 
                    'fitnessValue' => $fitnessValue    
                ];
                //print_r($fits);
            } else{
                echo ' (Not Fit)';
            }
            echo '<br><br>';
        }
        if($this->isFound($fits)){
            echo ' Found';
            echo '<br>';
        } else{
            echo ' >> Next Generation'; 
            echo '<br>';
        }
    }
}

class CrossOver{
    public $Population;

    function __construct($Population){
        $this->population = $Population;
    }

    function randomZerotoOne(){
        return (float) rand() / (float) getrandmax();
    }

    function generateCrossover(){       

        for($i=0;$i<parameters::population; $i++){
            $randomZerotoOne = $this->randomZerotoOne();
            if($randomZerotoOne < parameters::CROSSOVER_RATE){
                $parents[$i] = $randomZerotoOne;
            }
        }

        foreach(array_keys($parents) as $key){
            foreach(array_keys($parents) as $subkey){
                if($key !== $subkey){
                    $ret[] = [$key,$subkey];
                }
            }
            array_shift($parents);
        }
        return $ret;
        
    }

    function offspring($parent1, $parent2, $cutPointIndex, $offspring){
        $lengthOfGen = new individu;
        if($offspring==1){
            for($i = 0; $i < $lengthOfGen->countnumberofgen();$i++){
                if($i<= $cutPointIndex){
                    $ret[] = $parent1[$i];
                }
                if($i>$cutPointIndex){
                    $ret[] = $parent2[$i];
                }
            }
        }

        if($offspring==2){
            for($i = 0; $i < $lengthOfGen->countnumberofgen();$i++){
                if($i<= $cutPointIndex){
                    $ret[] = $parent2[$i];
                }
                if($i>$cutPointIndex){
                    $ret[] = $parent1[$i];
                }
            }  
        }
        return $ret;
    }
    
    function cutPointRandom(){
        $lengthOfGen = new individu();
        return rand(0,$lengthOfGen->countnumberofgen()-1);
    }

    function crossover(){
        $cutPointIndex = $this->cutPointRandom();
        //echo 'Cut Point Index: '. $cutPointIndex. '<br>';
        foreach($this->generateCrossover() as $listOfCrossover){
            //print_r($listOfCrossover); echo'<br>';
            $parent1 = $this->population[$listOfCrossover[0]];
            $parent2 = $this->population[$listOfCrossover[1]];
            // echo'<br> Parents:';
            // foreach($parent1 as $gen){
            //     echo $gen;
            // }

            // echo ' >< ';
            // foreach($parent2 as $gen){
            //     echo $gen;
            // }
            // //echo '<br><br>';

            // echo '<br>Offspring:';
            $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex,1);
            //print_r($offspring1);

           //echo '<br>';
            $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex,2);
            //print_r($offspring2);
            //echo '<br>';
            // foreach($offspring1 as $gen){
            //     echo $gen;
            // }

            // echo ' >< ';
            // foreach($offspring2 as $gen){
            //     echo $gen;
            // }
            // echo '<br>';
            $offsprings[] = $offspring1;
            $offsprings[] = $offspring2;
        }
        return $offsprings;
    }
    
}
class Randomizer{
    static function getRandomIndexOfGen(){
        return rand(0,(new Individu())->countnumberofgen()-1);
    }

    static function getRandomIndexOfIndividu(){
        return rand(0,parameters::population-1);
    }
}
class Mutation{
    function __construct($population){
        $this->population = $population;
    }

    function calculateMutationRate(){
        return 1/((new Individu())->countnumberofgen());
    }

    function calculateNumOfMutation(){
        return round($this->calculateMutationRate()*parameters::population);
    }

    function isMutation(){
        if($this->calculateNumOfMutation()>0){
            return TRUE;
        }
    }

    function generateMutation($valueOfGen){
        if($valueOfGen==0){
            return 1;
        } else{
            return 0;
        }
    }
    function mutation(){
        $nilai = $this->calculateNumOfMutation();
        $cek = $this->isMutation();
        
        if($cek){
            for($i=0;$i<$nilai;$i++){
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu();
                $indexOfGen = Randomizer::getRandomIndexOfGen();
                $selectedIndividu = $this->population[$indexOfIndividu];

                // echo 'Before Mutation: ';
                // print_r($selectedIndividu);
                // echo '<br>';

                $valueOfGen = $selectedIndividu[$indexOfGen];
                $mutatedGen = $this->generateMutation($valueOfGen);
                $selectedIndividu[$indexOfGen] = $mutatedGen;

                // echo 'After Mutation: ';
                // print_r($selectedIndividu);
                // echo '<br>';
                $ret[] = $selectedIndividu;
            }
            return $ret;
        }

    }
}

class Selection{
    function __construct($population, $combinedOffsprings){
        $this->population = $population;
        $this->combinedOffsprings = $combinedOffsprings;
    }

    function createTemporaryPopulation(){
        //echo ' Base Population : '. count($this->population).' &nbsp;';
        foreach($this->combinedOffsprings as $offspring){
            $this->population[] = $offspring;
        }
        //echo ' offspring: '. count($this->combinedOffsprings). ' Temporary: '. count($this->population);
        return $this->population;
    }

    function getVariableValue($basePopulation, $fitTemporaryPopulation){
        foreach($fitTemporaryPopulation as $val){
            $ret[] = $basePopulation[$val[1]];
        }
        return $ret;
    }

    function sortFitTemporaryPopoulation(){
        $tempPopoulation = $this->createTemporaryPopulation();
        $fitness = new fitnes;
        foreach($tempPopoulation as $key => $individu){
            $fitnessValue = $fitness->calculateFitnessValue($individu);
            if($fitness->isFit($fitnessValue)){
                // echo $fitnessValue.' '.$key.'<br>';
                $fitTemporaryPopulation[]=[
                    $fitnessValue,
                    $key
                ];
            }
        }
        rsort($fitTemporaryPopulation);
        // foreach($fitTemporaryPopulation as $val){
        //     print_r($val).'<br>';
        // }
        $fitTemporaryPopulation = array_slice($fitTemporaryPopulation, 0, parameters::population);
        // echo '<p></p>'. print_r($fitTemporaryPopulation);
        return $this->getVariableValue($tempPopoulation, $fitTemporaryPopulation);
    }

    function selectingIndividus(){
        // echo '<p><p> Temporary Population <br>';
        // print_r($this->createTemporaryPopulation());
        $selected = $this->sortFitTemporaryPopoulation();
        echo '<p></p>';
        print_r($selected);

    }
}
// $katalog = new cataloge;
// print_r($katalog->product());

$p = new population;
$InitialPopulation = $p->createrandompopulation();

$f = new fitnes;
$f->fitnessevaluation($InitialPopulation);
$in = new individu;
//print_r($in->createrandomindividu()); 

$crossover = new CrossOver($InitialPopulation);
$crossoverOffsprings = $crossover->crossover();

// echo 'Crossover offsprings: <br>';
// print_r($crossoverOffsprings);
// echo '<br><br>';

$mutation = new Mutation($InitialPopulation);
if($mutation->mutation()){
    // echo ' ada mutasi';
    $mutationOffsprings = $mutation->mutation();
    // echo 'Mutation off Spring: ';
    // print_r($mutationOffsprings);
    // echo '<p><p>';
    foreach($mutationOffsprings as $mutationOffspring){
        $crossoverOffsprings[] = $mutationOffspring;
    }
}
// echo 'Mutation offsprings: <br>';
// print_r($crossoverOffsprings);

$selection = new Selection($InitialPopulation, $crossoverOffsprings);
$selection->selectingIndividus();

?>

