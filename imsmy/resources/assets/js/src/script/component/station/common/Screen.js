import React, { Component } from 'react'
import { Select,Button } from 'antd'
const Option = Select.Option
import FetchPost from 'utils/fetch'
//屏蔽仓 - 测试城市联动

class Screen extends Component {
  constructor(props){
    super(props)
    this.state={
      country:[],
      province:[],
      city:[],
      county:[],
      provinceDisabled:true,
      cityDisabled:true,
      countyDisabled:true,
      countryValue:"请选择国家",
      provinceValue:"请选择省份", //省份
      cityValue:"请选择城市", //城市
      countyValue:"请选择区(县)", //区县
    }
  }
  //获取国家
  handleCountry=()=>{
    FetchPost.post({
      uri:'/api/admins/fodder/issue/fragment/addresscountry',
      callback:(res)=>{
        this.setState({
          country:res.data
        })
      }
    })
  }
  //改变国家  获取省份或者城市
  handleChangeCountry=(value)=>{
    const GetCountryName = this.state.country
    for(let i=0;i<GetCountryName.length;i++){
      if(GetCountryName[i].id === value){
        this.setState({
          countryValue:GetCountryName[i].Name
        })
      }
    }
      this.setState({
        province:[],
        city:[],
        county:[],
        provinceDisabled:true,
        cityDisabled:true,
        countyDisabled:true,
        provinceValue:"请选择省份", //省份
        cityValue:"请选择城市", //城市
        countyValue:"请选择区(县)", //区县
      })
      let formData=new FormData()
      formData.append('id',value)
      FetchPost.post({
        uri:'/api/admins/fodder/issue/fragment/addressprovince',
        callback:(res)=>{
          if(res.data.length>0){
            this.setState({
              province:res.data,
              provinceDisabled:false,
              provinceValue:"请选择省份"
            })
          }else{
            this.setState({
              province:[],
              provinceDisabled:true,
              provinceValue:"请选择省份",
            })
            FetchPost.post({
              uri:'/api/admins/fodder/issue/fragment/addressstate',
              callback:(res)=>{
                if(res.data.length>0){
                  this.setState({
                    city:res.data,
                    cityDisabled:false,
                    cityValue:"请选择城市"
                  })
                }else{
                  this.setState({
                    city:[],
                    cityDisabled:true,
                    cityValue:"请选择城市",
                  })
                }
              },
              formData:formData
            })
          }
        },
        formData:formData
      })
  }
  //改变省份 获取城市
  handleChangeProvince=(value)=>{
    if(this.state.provinceDisabled===false){
      const GetProvinceName = this.state.province
      for(let i=0;i<GetProvinceName.length;i++){
        if(GetProvinceName[i].id === value){
          this.setState({
            provinceValue:GetProvinceName[i].Name
          })
        }
      }
      this.setState({
        city:[],
        county:[],
        cityDisabled:true,
        countyDisabled:true,
        cityValue:"请选择城市", //城市
        countyValue:"请选择区(县)", //区县
      })
      let formData= new FormData()
      formData.append('id',value)
      FetchPost.post({
        uri:'/api/admins/fodder/issue/fragment/addresscity',
        callback:(res)=>{
          if(res.data.length>0){
            this.setState({
              city:res.data,
              cityDisabled:false,
              cityValue:"请选择城市"
            })
          }else{
            this.setState({
              city:[],
              cityDisabled:true,
              cityValue:"请选择城市"
            })
          }
        },
        formData:formData
      })
    }

  }
  //改变城市 获取区/县
  handleChangeCity=(value)=>{
    if(this.state.cityDisabled === false){
      const GetCitySet = this.state.city
      const GetArraySolo = []
      for(let i=0;i<GetCitySet.length;i++){
         if(GetCitySet[i].id === value){
           GetArraySolo.push(GetCitySet[i])
           this.setState({
             cityValue:GetCitySet[i].Name
           })
         }
      }
      this.setState({
        county:[],
        countyDisabled:true,
        countyValue:"请选择区(县)",
      })
      if(GetArraySolo[0].Pid){
        const Code = GetArraySolo[0].Code
        const Pid = GetArraySolo[0].Pid
        const Tid = GetArraySolo[0].Tid
        let formData = new FormData()
        formData.append('Code',Code)
        formData.append('Pid',Pid)
        formData.append('Tid',Tid)
        FetchPost.post({
          uri:'/api/admins/fodder/issue/fragment/addresscounty',
          callback:(res)=>{
            if(res.data.length>0){
              this.setState({
                county:res.data,
                countyDisabled:false,
                countyValue:"请选择区(县)",
              })
            }else{
              this.setState({
                county:[],
                countyDisabled:true,
                countyValue:"请选择区(县)",
              })
            }
          },
          formData:formData
        })
      }
    }
  }
  //改变区县
  handleChangeCounty=(value)=>{
    if(this.state.countyDisabled===false){
      const GetCountyName = this.state.county
      for(let i=0;i<GetCountyName.length;i++){
        if(GetCountyName[i].id === value){
          this.setState({
            countyValue:GetCountyName[i].Name
          })
        }
      }
    }
  }
  test=()=>{
    console.log(
      this.state.countryValue+"-"+this.state.provinceValue+"-"+this.state.cityValue+"-"+this.state.countyValue

    );
  }

  render(){
    return(
      <div style={{background:"#fff",border:"1px solid #000",height:500,paddingTop:20}}>
        <Select defaultValue="请选择国家" style={{ width: 150,marginLeft:10 }}
          value={this.state.countryValue}
          onChange={this.handleChangeCountry}
          onFocus={this.handleCountry}
          >
          {
            this.state.country.map((value,index)=>{
              return(
                <Option value={value.id} key={index}>{value.Name}</Option>
              )
            })
          }
        </Select>
        <Select defaultValue='请选择省份'  style={{ width: 150,marginLeft:10 }}
            disabled={this.state.provinceDisabled}
            value={this.state.provinceValue}
            onChange={this.handleChangeProvince}
          >
          {
            this.state.province.map((value,index)=>{
              return(
                <Option value={value.id} key={index}>{value.Name}</Option>
              )
            })
          }
        </Select>
        <Select defaultValue="请选择城市"  style={{ width: 150,marginLeft:10 }}
              value={this.state.cityValue}
             onChange={this.handleChangeCity}
             disabled={this.state.cityDisabled}
             // onFocus={this.testone}
          >
            {
              this.state.city.map((value,index)=>{
                return(
                  <Option value={value.id} key={index}>{value.Name}</Option>
                )
              })
            }
        </Select>
        <Select defaultValue="请选择区(县)"  style={{ width: 150,marginLeft:10 }}
            value={this.state.countyValue}
            onChange={this.handleChangeCounty}
            disabled={this.state.countyDisabled}
          >
            {
              this.state.county.map((value,index)=>{
                return(
                  <Option value={value.id} key={index}>{value.Name}</Option>
                )
              })
            }
        </Select>
        <Button onClick={this.test}>55454</Button>
      </div>
    )
  }

}

export default Screen
