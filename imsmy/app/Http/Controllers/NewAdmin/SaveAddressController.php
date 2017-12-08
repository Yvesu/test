<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\Address\AddressCity;
use App\Models\Address\AddressCountry;
use App\Models\Address\AddressCounty;
use App\Models\Address\AddressProvince;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SaveAddressController extends Controller
{
    //
    public function saveAddress()
    {
        try{
            $str = \Storage::get('city.txt');

            $str = str_replace("/>","/city >",$str);
//        $str = str_replace("/","",$str);
            $str = str_replace('<', '[', $str);
            $str = str_replace('>', ']', $str);
            $str = str_replace("\r\n","",$str);
            $str = str_replace("[Location]","",$str);
            $str = str_replace("[/Location]","",$str);
//        echo $str = str_replace(' />', ']', $str);
//        print_r($str) ;
//        $arr = explode('[Location]',$str);
//        unset($arr[0],$arr[2]);

            $arr = explode('[CountryRegion',$str);
            unset($arr[0]);
            foreach ($arr as $k => $v)
            {
                $arr[$k] = '[CountryRegion'.$v;
                if(strlen($arr[$k])<100)
                {
                    $arr[$k] = str_replace('/city','/CountryRegion',$arr[$k]);
                }
                $patterns = "/\]\s{1,10}\[/";
                $arr[$k] = preg_replace($patterns,'][',$arr[$k]);

            }
            foreach ($arr as $k => $v)
            {

                $patternscountry = "/\[State(.*?)\]\[\/State\]/";
                $country[$k]= preg_replace($patternscountry,'',$v);
                $country[$k]= preg_replace($patternscountry,'',$v);
//                print_r(preg_match($patternscountry,$v));
                $patternscountry1[0] = "/\[CountryRegion/";
                $patternscountry1[1] = "/\/CountryRegion.*?\]/";
                $patternscountry1[2] = "/\[\/CountryRegion\]/";
                $patternscountry1[3] = "/\"/";
                $replacement[0] = '';
                $replacement[1] = '';
                $replacement[2] = '';
                $replacement[3] = '';
                $country[$k] = preg_replace($patternscountry1,$replacement,$country[$k]);
                $patternscountry2 = '/]{0,1}\[{0,1}/';
                $country[$k] = preg_replace($patternscountry2,'',$country[$k]);
                $country[$k] = trim($country[$k]);
                $country[$k] = explode(' ',$country[$k]);
                foreach($country[$k] as $kk => $vv)
                {
                    $arr1 = explode('=',$vv);
                    unset($country[$k][0],$country[$k][1]);
                    $country[$k][$arr1[0]] = $arr1[1];
                }

            }


            foreach($arr as $k => $v)
            {
                $patternscountrystate[0] = "/\[CountryRegion.*?\]/";
                $patternscountrystate[1] = "/\[City.*?\/[C|c]ity.*?\]/";
                $patternscountrystate[2] = "/\[{0,1}\/CountryRegion\]/";
                $patternscountrystate[3] = "/\[Region.*?\/[C|c]ity\]/";
                $patternscountrystate[4] = "/\[\/City]/";
                $patternscountrystate[5] = "/\"/";
                $replacement[0] = '';
                $replacement[1] = '';
                $replacement[2] = '';
                $replacement[3] = '';
                $replacement[4] = '';
                $replacement[5] = '';
                $state[$k] = preg_replace($patternscountrystate,$replacement,$v);
                $state[$k] = explode('[/State]',$state[$k]);
                foreach($state[$k] as $kk => $vv)
                {
                    $patternscountrystate1[0] = "/\[State\s/";
                    $patternscountrystate1[1] = "/\[State]/";
                    $patternscountrystate1[3] = "/\]/";
                    $replacement[0] = '';
                    $replacement[1] = '';
                    $replacement[2] = '';
                    $state[$k][$kk] =preg_replace($patternscountrystate1,$replacement,$vv);
                }
                array_pop($state[$k]);
                if(empty($state[$k][0]))
                {
                    unset($state[$k]);
                }else{
                    foreach($state[$k] as $kk => $vv)
                    {
                        $state[$k][$kk] = explode(' ',$vv);
                        foreach($state[$k][$kk] as $kkk => $vvv)
                        {
                            $state[$k][$kk][$kkk] = explode('=',$vvv);

                            $state[$k][$kk] [$state[$k][$kk][$kkk][0]] = $state[$k][$kk][$kkk][1];
                            unset($state[$k][$kk][0],$state[$k][$kk][1]);
                            $state[$k][$kk]['Pid'] = $k;
//                        $state[$k][$kk]['pid'] = $country[$k]['Code'];
//                        echo '<pre>';
//                        print_r($state[$k][$kk][$kkk][1]);
////                        print_r($state[$k][$kk][$kkk]);
//                        echo '</pre>';
                        }


                    }
                }

            }

            $m = 0;

            foreach ($state as $k => $v)
            {
                foreach($state[$k] as $kk => $vv)
                {
                    $finallyState[$m] = $vv;
                    $m++;
                }
            }




            foreach($arr as $k => $v)
            {
                $patternscity[0] = "/\[CountryRegion.*?\]/";
//            $patternscity[1] = "/\[\/{0,1}State.*?\]/";
                $patternscity[2] = "/\[\/CountryRegion\]/";
                $patternscity[3] = "/\[Region.*?\]/";
                $replacementcity[0] = '';
                $replacementcity[1] = '';
                $replacementcity[2] = '';
                $replacementcity[3] = '';
                $city[$k] = preg_replace($patternscity,$replacementcity,$arr[$k]);
                $city[$k] = explode('[/State]',$city[$k]);
                array_pop($city[$k]);
                $patternscity1[0] = '/State\sName=".*?"\sCode=/';
                $patternscity1[1] = "/\[\/{0,1}State\]/";
                $patternscity1[2] = "/\/{0,1}[C|c]ity/";
                $patternscity1[3] = "/\[\]/";
                $patternscity1[4] = "/\[/";
                $city[$k] = preg_replace($patternscity1,$replacementcity,$city[$k]);
                foreach ($city[$k] as $kk => $vv)
                {

                    $city[$k][$kk] = explode(']',$city[$k][$kk]);
                    array_pop($city[$k][$kk]);
                    foreach ($city[$k][$kk] as $kkk => $vvv)
                    {
                        if($kkk != 0 && strlen($city[$k][$kk][0])<6 )
                        {
                            $city[$k][$kk][$kkk] = trim($city[$k][$kk][$kkk]).' '.$city[$k][$kk][0];
                        }
                        $patternscity2[0] = "/Name=/";
                        $patternscity2[1] = '/\"/';
                        $patternscity2[2] = '/Code=/';
                        $city[$k][$kk][$kkk] = trim(preg_replace($patternscity2,' ',$city[$k][$kk][$kkk]));
                        $patternscity3 = "/\s{1,5}/";
                        $city[$k][$kk][$kkk] = preg_replace($patternscity3,' ',$city[$k][$kk][$kkk]);
                    }
                    unset($city[$k][$kk][0]);
                    if(empty($city[$k][$kk])){
                        unset($city[$k][$kk]);
                    }

//               $city[$k][$kk] = preg_replace('[','',$city[$k][$kk]);
                }
                if(empty($city[$k]))
                {
                    unset($city[$k]);
                }

            }
//        echo '<pre>';
//            print_r($city);
//        echo '</pre>';
//        die();
            $n = 0;
            foreach ($city as $q => $m)
            {
                foreach ($city[$q] as $qq => $mm)
                {
                    foreach ($city[$q][$qq] as $qqq => $mmm)
                        $city[$q][$qq][$qqq] = explode(' ',$city[$q][$qq][$qqq].' '.$q);



                }
            }

            foreach($city as $k => $v)
            {
                foreach($city[$k] as $kk => $vv)
                {
                    foreach ($city[$k][$kk] as $kkk => $vvv)
                    {
                        $trueCity[$n] = $vvv;
                        if(count($trueCity[$n])>3)
                        {
                            $finallyCity[$n]['Name'] = $trueCity[$n][0];
                            $finallyCity[$n]['Code'] = $trueCity[$n][1];
                            $finallyCity[$n]['Pid'] = $trueCity[$n][2];
                            $finallyCity[$n]['Tid'] = $trueCity[$n][3];
                        }else{
                            $finallyCity[$n]['Name'] = $trueCity[$n][0];
                            $finallyCity[$n]['Code'] = $trueCity[$n][1];
                            $finallyCity[$n]['Tid'] = $trueCity[$n][2];
                        }


                        $n++;
                    }
                }
            }



            foreach($arr as $k => $v)
            {
                $patternscounty1 = "/CountryRegion/";
                $replacement = '';
                $county[$k] = preg_replace($patternscounty1,$replacement,$arr[$k]);

                if(strpos($county[$k],'Region') === false)
                {
                    unset($county[$k]);
                }


            }
            $county[1] = explode('[State',$county[1]);
            $county = $county[1];
            foreach($county as $k => $v)
            {
                $county[$k] = explode('[City',$county[$k]);
                if($k>0)
                {
                    foreach($county[$k] as $kk => $vv)
                    {
                        if((strpos($county[$k][$kk],'Region') === false) && $kk>0)
                        {
                            unset($county[$k][$kk]);

                            if(count($county[$k]) == 1) unset($county[$k]);


                        }



                    }
                }

            }

            foreach($county as $k => $v)
            {
                foreach($county[$k] as $kk => $vv)
                {
                    $county[$k][$kk] = explode('Region',$county[$k][$kk]);
                    foreach($county[$k][$kk] as $kkk => $vvv)
                    {
                        $patternscounty2[0] = "/[\[|\]]/";
                        $patternscounty2[1] = "/\/C|city/";
                        $patternscounty2[2] = "/\//";
                        $replacement3[0] = '';
                        $replacement3[1] = '';
                        $replacement3[2] = '';
                        $county[$k][$kk][$kkk] = trim(preg_replace($patternscounty2,$replacement3,$county[$k][$kk][$kkk]));
                        if($kkk<1)
                        {
                            $patternscountry3 = "/Name=\".*?\" Code=/";
                            $replacement4 = "";
                            $county[$k][$kk][$kkk] = trim(preg_replace($patternscountry3,$replacement4,$county[$k][$kk][$kkk]),'"');
                        }else{
                            $county[$k][$kk][$kkk] = explode(' ',$county[$k][$kk][$kkk]);
                            $county[$k][$kk][$kkk]['Pid'] = $county[$k][$kk][0];
                            $county[$k][$kk][$kkk]['Tid'] = $county[$k][0][0];
                            $county[$k][$kk][$kkk]['Cid'] = $county[0][0][0];
                            $patternscountry4[0] = "/Name=/";
                            $patternscountry4[1] = "/Code=/";
                            $patternscountry4[2] = "/\"/";
                            $replacement5[0] = '';
                            $replacement5[1] = '';
                            $replacement5[2] = '';
                            $county[$k][$kk][$kkk]['Name'] = preg_replace($patternscountry4,$replacement5,$county[$k][$kk][$kkk][0]);
                            $county[$k][$kk][$kkk]['code'] = preg_replace($patternscountry4,$replacement5,$county[$k][$kk][$kkk][1]);
                            unset($county[$k][$kk][$kkk][0],$county[$k][$kk][$kkk][1]);

                        }
                    }
                }


            }

            unset($county[0]);
            $p = 0;
            foreach($county as $k => $v)
            {
                unset($county[$k][0]);
                foreach($county[$k] as $kk => $v)
                {
                    unset($county[$k][$kk][0]);

                }
            }

            foreach ($county as $k => $v) {
                foreach ($county[$k] as $kk => $vv) {
                    foreach ($county[$k][$kk] as $kkk => $vvv){
                        $finallyCounty[$p] = $county[$k][$kk][$kkk];
                        $p++;
                    }
                }
            }

            foreach($finallyCounty as $k => $v)
            {
                foreach($finallyCounty[$k] as $kk => $vv)
                {
                    if(($kk==2)||($kk==3))
                    {
                        unset($finallyCounty[$k][$kk]);
                    }
                }
            }

//        echo '<pre>';
//            print_r($state);
//            print_r($finallyState);
//            print_r($arr);
//            print_r($city);
//            print_r($finallyCity);
//            print_r($county);
//        print_r($finallyCounty);
//            print_r($country);
//        echo $str;
//        echo '</pre>';

            DB::beginTransaction();

            foreach($country as $k => $v)
            {
                $addresscountry = new AddressCountry;
                $addresscountry->Name = $country[$k]['Name'];
                $addresscountry->Code = $country[$k]['Code'];
                $addresscountry->time_add = time();
                $addresscountry->time_update = time();
                $addresscountry->save();
            }

            foreach($finallyState as $k => $v)
            {
                $addressprovince = new AddressProvince;
                $addressprovince->Name = $finallyState[$k]['Name'];
                $addressprovince->Code = $finallyState[$k]['Code'];
                $addressprovince->Pid = $finallyState[$k]['Pid'];
                $addressprovince->time_add = time();
                $addressprovince->time_update = time();
                $addressprovince->save();
            }

            foreach($finallyCity as $k => $v)
            {
                $addresscity = new AddressCity;
                $addresscity->Name = $finallyCity[$k]['Name'];
                $addresscity->Code = $finallyCity[$k]['Code'];
                if(count($v)==3)
                {
                    $addresscity->Tid = $finallyCity[$k]['Tid'];
                }else{
                    $addresscity->Tid = $finallyCity[$k]['Tid'];
                    $addresscity->Pid = $finallyCity[$k]['Pid'];
                }
                $addresscity->time_add = time();
                $addresscity->time_update = time();
                $addresscity->save();
            }

            foreach($finallyCounty as $k => $v)
            {
                $addresscounty = new AddressCounty;
                $addresscounty->Name = $finallyCounty[$k]['Name'];
                $addresscounty->Code = $finallyCounty[$k]['code'];
                $addresscounty->Pid = $finallyCounty[$k]['Pid'];
                $addresscounty->Tid = $finallyCounty[$k]['Tid'];
                $addresscounty->Cid = $finallyCounty[$k]['Cid'];
                $addresscounty->time_add = time();
                $addresscounty->time_update = time();
                $addresscounty->save();
            }
            DB::commit();
        }catch (ModelNotFoundException $e) {
            DB::rollBack();
        }
    }
}
