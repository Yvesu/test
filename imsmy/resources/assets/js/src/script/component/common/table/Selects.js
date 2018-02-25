import React, { Component } from 'react'
// import {hashHistory} from 'react-router'
import Fetch from 'utils/fetch'
//选项组件
import { Input, Button, Icon, Select } from 'antd'
const Search = Input.Search

class Selects extends Component {
  constructor(props){
    super(props)
    this.state={
      type:[], //类型
      operator:[],//操作人员
      time:[],//时间
      duration:[],//时长
      integral:[],//下载费用
      count:[],//下载量

      userType:[], //用户类型
      thirdType:[],//第三方类型
      userVipType:[],//vip用户类型
      auditor:[], //审核人
      fans:[],//粉丝数量
      playCount:[],//用户页的播放量
      works:[],//作品数
      userIntegral:[],//资产

      searchValue:"", //搜索关键字
      typeValue:'', //类型
      operatorValue:"",//操作人员
      timeValue:'', //选择的事件
      durationValue:'', //时长
      integralValue:"",//下载费用
      countValue:'',//下载量

      userValue:"", //用户类型
      thirdValue:'', //第三方类型
      userVipValue:"",//vip用户类型
      auditorValue:'',//审核人
      fansValue:'', //选择的事件
      playCountValue:'', //用户页的播放量
      worksValue:"",//作品数
      userIntegralValue:''//用户资产

    }
    // console.log(this.props,'select');
  }
  hanldSelectType=()=>{
    Fetch.post({
      uri:this.props.type,
      callback:(res)=>{
        this.setState({
          type:res.data
        })
      }
    })
  }
  handleSelectOperator=()=>{
    Fetch.post({
      uri:this.props.operator,
      callback:(res)=>{
        this.setState({
          operator:res.data
        })
      }
    })
  }
  handleSelectTime=()=>{
    Fetch.post({
      uri:this.props.time,
      callback:(res)=>{
        this.setState({
          time:res.data
        })
      }
    })
  }
  handleSelectDuration=()=>{
    Fetch.post({
      uri:this.props.duration,
      callback:(res)=>{
        this.setState({
          duration:res.data
        })
      }
    })
  }
  handleSelectIntegral=()=>{
    Fetch.post({
      uri:this.props.integral,
      callback:(res)=>{
        this.setState({
          integral:res.data
        })
      }
    })
  }
  handleSelectCount=()=>{
    Fetch.post({
      uri:this.props.count,
      callback:(res)=>{
        this.setState({
          count:res.data
        })
      }
    })
  }
  // 用户页onfoucs事件
  handleSelectUserType=()=>{
    Fetch.post({
      uri:this.props.user,
      callback:(res)=>{
        this.setState({
          userType:res.data
        })
      }
    })
  }
  handleSelectThirdType=()=>{
    Fetch.post({
      uri:this.props.third,
      callback:(res)=>{
        this.setState({
          thirdType:res.data
        })
      }
    })
  }
  handleSelectUserVipType=()=>{
    Fetch.post({
      uri:this.props.userVip,
      callback:(res)=>{
        this.setState({
          userVipType:res.data
        })
      }
    })
  }
  handleSelectAuditor=()=>{
    Fetch.post({
      uri:this.props.auditor,
      callback:(res)=>{
        // console.log(res,'jigou ');
        this.setState({
          auditor:res.data
        })
      }
    })
  }
  handleSelectFans=()=>{
    Fetch.post({
      uri:this.props.fans,
      callback:(res)=>{
        this.setState({
          fans:res.data
        })
      }
    })
  }
  handleSelectPlayCount=()=>{
    Fetch.post({
      uri:this.props.playCount,
      callback:(res)=>{
        this.setState({
          playCount:res.data
        })
      }
    })
  }
  handleSelectWorks=()=>{
    Fetch.post({
      uri:this.props.works,
      callback:(res)=>{
        this.setState({
          works:res.data
        })
      }
    })
  }
  handleSelectUserIntegral=()=>{
    Fetch.post({
      uri:this.props.assets,
      callback:(res)=>{
        this.setState({
          userIntegral:res.data
        })
      }
    })
  }
  // 用户页结束
  handleSearchValue=(e)=>{
    this.setState({
      searchValue:e.target.value
    })
  }
  handleTypeValue=(value)=>{
    this.setState({
      typeValue:value
    })
    // this.props.typeValue(value)


  }
  handleOperatorValue=(value)=>{
    this.setState({
      operatorValue:value
    })
    // this.props.operatorValue(value)

  }
  handleTimeValue=(value)=>{
    this.setState({
      timeValue:value
    })
    // this.props.timeValue(value)

  }
  handleDurationValue=(value)=>{
    this.setState({
      durationValue:value
    })
  // this.props.durationValue(value)
  }
  handleIntegralValue=(value)=>{
    this.setState({
      integralValue:value
    })
  }
  handleCountValue=(value)=>{
    this.setState({
      countValue:value
    })
  }

  // 用户页
  handleUserTypeValue=(value)=>{
    this.setState({
      userValue:value
    })
  }
  handleThirdTypeValue=(value)=>{
    this.setState({
      thirdValue:value
    })
  }
  handleUserVipValue=(value)=>{
    this.setState({
      userVipValue:value
    })
  }
  handleAuditorValue=(value)=>{
    this.setState({
      auditorValue:value
    })
  }
  handleFansValue=(value)=>{
    this.setState({
      fansValue:value
    })
  }
  handlePlayCountValue=(value)=>{
    this.setState({
      playCountValue:value
    })
  }
  handleWorksValue=(value)=>{
    this.setState({
      worksValue:value
    })
  }
  handleUserIntegralValue=(value)=>{
    this.setState({
      userIntegralValue:value
    })
  }

  handleChangeTable=()=>{
    if(this.props.searchValue){
      this.props.searchValue(this.state.searchValue)
    }
    if(this.props.typeValue){
      this.props.typeValue(this.state.typeValue)
    }
    if(this.props.operatorValue){
      this.props.operatorValue(this.state.operatorValue)
    }
    if(this.props.timeValue){
      this.props.timeValue(this.state.timeValue)
    }
    if(this.props.durationValue){
      this.props.durationValue(this.state.durationValue)
    }
    if(this.props.integralValue){

      this.props.integralValue(this.state.integralValue)
    }
    if(this.props.countValue){
      this.props.countValue(this.state.countValue)
    }
    // 用户页
    if(this.props.userType){
      this.props.userType(this.state.userValue)
    }
    if(this.props.thirdType){
      this.props.thirdType(this.state.thirdValue)
    }
    if(this.props.userVipType){
      this.props.userVipType(this.state.userVipValue)
    }
    if(this.props.auditorValue){
      this.props.auditorValue(this.state.auditorValue)
    }
    if(this.props.fansValue){
      this.props.fansValue(this.state.fansValue)
    }
    if(this.props.playCountValue){
      this.props.playCountValue(this.state.playCountValue)
    }
    if(this.props.worksValue){
      this.props.worksValue(this.state.worksValue)
    }
    if(this.props.assetsValue){
      this.props.assetsValue(this.state.userIntegralValue)
    }
  }

  handleChangeSearchValue=(e)=>{
    this.setState({
      searchValue:e.target.value
    })
    this.props.searchValue(e.target.value)
  }

  render(){
    return(
        <div className="select_filter">
              {this.props.title}
            <p>
              <span>搜索：
                <Search placeholder={this.props.searchPlaceholder}  style={{ width: "15%" }}
                    onSearch={this.handleSearchValue}
                    onChange={this.handleSearchValue}
                    onPressEnter={this.handleChangeSearchValue}
                />
              </span>
              {
                this.props.type!==null ?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                   placeholder='分类:电影'
                    onFocus={this.hanldSelectType}
                    onChange={this.handleTypeValue}
                    allowClear={true}
                  >
                    {
                      this.state.type.map((value,index)=>{
                        return(
                          <Option value={value.id}>{value.type}</Option>
                        )
                      })
                    }
                  </Select> : null
              }

              {
                this.props.operator !==null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                  placeholder='操作员:所有人'
                    onFocus={this.handleSelectOperator}
                    onChange={this.handleOperatorValue}
                    allowClear={true}
                  >
                  {
                    this.state.operator.map((value,index)=>{
                      return(
                        <Option value={value.operator_id}>{value.operator_name}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {
              this.props.time !==null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='时间: 不限时间'
                    onFocus={this.handleSelectTime}
                    onChange={this.handleTimeValue}
                    allowClear={true}

                  >
                  {
                    this.state.time.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }

              {
                this.props.duration !==null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='时长:不限'
                    onFocus={this.handleSelectDuration}
                    onChange={this.handleDurationValue}
                    allowClear={true}

                  >
                  {
                    this.state.duration.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }

              {
                this.props.integral !==null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='费用: 免费'
                    onFocus={this.handleSelectIntegral}
                    onChange={this.handleIntegralValue}
                    allowClear={true}
                  >
                  {
                    this.state.integral.map((value,index)=>{
                      return(
                        <Option value={value.intergal}>{value.intergal}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {
                this.props.count !==null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='下载量: 1万以上'
                    onFocus={this.handleSelectCount}
                    onChange={this.handleCountValue}
                    allowClear={true}
                  >
                  {
                    this.state.count.map((value,index)=>{
                      return(
                        <Option value={value.count}>{value.count}</Option>
                      )
                    })
                  }
                </Select> : null
              }

              {/* 用户页 */}
              {this.props.user !== null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='角色：全部'
                    onFocus={this.handleSelectUserType}
                    onChange={this.handleUserTypeValue}
                    allowClear={true}
                  >
                  {
                    this.state.userType.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {this.props.third !== null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='第三方：微信'
                    onFocus={this.handleSelectThirdType}
                    onChange={this.handleThirdTypeValue}
                    allowClear={true}
                  >
                  {
                    this.state.thirdType.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {this.props.userVip !== null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='VIP：全部'
                    onFocus={this.handleSelectUserVipType}
                    onChange={this.handleUserVipValue}
                    allowClear={true}
                  >
                  {
                    this.state.userVipType.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {this.props.auditor !== null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='审核人：全部'
                    onFocus={this.handleSelectAuditor}
                    onChange={this.handleAuditorValue}
                    allowClear={true}
                  >
                  {
                    this.state.auditor.map((value,index)=>{
                      return(
                        <Option value={value.id}>{value.name}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {this.props.fans !== null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='粉丝：1000以上'
                    onFocus={this.handleSelectFans}
                    onChange={this.handleFansValue}
                    allowClear={true}
                  >
                  {
                    this.state.fans.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {this.props.playCount !== null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='播放量：不限'
                    onFocus={this.handleSelectPlayCount}
                    onChange={this.handlePlayCountValue}
                    allowClear={true}
                  >
                  {
                    this.state.playCount.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {this.props.works !== null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='作品：10个以上'
                    onFocus={this.handleSelectWorks}
                    onChange={this.handleWorksValue}
                    allowClear={true}
                  >
                  {
                    this.state.works.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {
                this.props.assets !==null?
                <Select style={{width: "11.5%",fontSize:12,marginLeft:"2%"}}
                    placeholder='资产：1000以上'
                    onFocus={this.handleSelectUserIntegral}
                    onChange={this.handleUserIntegralValue}
                    allowClear={true}
                  >
                  {
                    this.state.userIntegral.map((value,index)=>{
                      return(
                        <Option value={value.label}>{value.des}</Option>
                      )
                    })
                  }
                </Select> : null
              }
              {/* 用户页结束 - 是资产页 */}

              <Button type="primary" onClick={this.handleChangeTable}
                style={{width: "8%", marginLeft:"3%"}}>确定</Button>
            </p>
        </div>

    )
  }

}

export default Selects
