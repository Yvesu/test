import React, { Component } from 'react'
import { Link } from 'react-router'
import { Icon } from 'antd'

import Selects from './Selects'
import TableContainer from './TableContainer'


//片段全部
class SelectAndTable extends Component {
  constructor(props){
    super(props)
    this.state={
      search:"",//搜索
      type:"", //类型
      operator:"", //操作员
      time:"", //时间
      duration:"", //时长
      integral:'', // 下载费用  //资产
      count:"", // 下载量
      //用户页的选项
      userType:'',//用户类型
      thirdType:'',//第三方类型
      vipType:'', // 用户第三方等级
      auditor:'',//审核人
      fans:'', //粉丝数量
      playCount:'', //播放量
      works:'', //作品数
    }
  }


handleChangeNameValue=(value)=>{
  this.setState({
    search:value
  })
}
handleChangeType=(value)=>{
  this.setState({
    type:value
  })
}
handleChangeOperator=(value)=>{
  this.setState({
    operator:value
  })
}
handleChangeDuration=(value)=>{
  this.setState({
    duration:value
  })
}
handleChangeIntegral=(value)=>{
  this.setState({
    integral:value
  })
}
handleChangeTime=(value)=>{
  this.setState({
    time:value
  })
}
handleChangeCount=(value)=>{
  this.setState({
    count:value
  })
}
handleChangeUserType=(value)=>{
  this.setState({
    userType:value
  })
}
handleChangeThirdType=(value)=>{
  this.setState({
    thirdType:value
  })
}
handleChangeUserVipType=(value)=>{
  this.setState({
    vipType:value
  })
}
handleChangeAuditor=(value)=>{
  this.setState({
    auditor:value
  })
}
handleChangeFansCount=(value)=>{
  this.setState({
    fans:value
  })
}
handleChangePlayCount=(value)=>{
  this.setState({
    playCount:value
  })
}
handleChangeWorksCount=(value)=>{
  this.setState({
    works:value
  })
}


  render(){
    return(
      <div className='select_and_table'>
        <Selects
          title={this.props.title? this.props.title : null}
          // uri={this.props.selectUri? this.props.selectUri : null }
          searchPlaceholder={this.props.searchPlaceholder? this.props.searchPlaceholder : ''}
          type={this.props.type? this.props.type : null }
          operator={this.props.operator? this.props.operator : null }
          time={this.props.time? this.props.time : null }
          duration={this.props.duration? this.props.duration : null }
          integral={this.props.integral? this.props.integral : null }
          count={this.props.count? this.props.count : null }
          user={this.props.user? this.props.user : null }
          third={this.props.third? this.props.third : null }
          userVip={this.props.userVip? this.props.userVip : null }
          auditor={this.props.auditor? this.props.auditor : null }
          fans={this.props.fans? this.props.fans : null }
          playCount={this.props.playCount? this.props.playCount : null }
          works={this.props.works? this.props.works : null }
          assets={this.props.assets? this.props.assets : null }
          typeValue={this.props.type? this.handleChangeType : null }
          searchValue={this.handleChangeNameValue}
          timeValue={this.props.time? this.handleChangeTime : null }
          operatorValue={this.props.operator? this.handleChangeOperator : null }
          durationValue={this.props.duration? this.handleChangeDuration : null }
          integralValue={this.props.integral? this.handleChangeIntegral : null }
          countValue={this.props.count? this.handleChangeCount : null }
          userType={this.props.user? this.handleChangeUserType : null }
          thirdType={this.props.third? this.handleChangeThirdType : null }
          userVipType={this.props.userVip? this.handleChangeUserVipType : null }
          auditorValue={this.props.auditor? this.handleChangeAuditor : null }
          playCountValue={this.props.playCount? this.handleChangePlayCount : null }
          worksValue={this.props.works? this.handleChangeWorksCount : null }
          fansValue={this.props.fans? this.handleChangeFansCount : null }
          assetsValue={this.props.assets? this.handleChangeIntegral : null } //资产用的就是下载费用的方式
        />
        <TableContainer
          columns={this.props.columns}
          uri={this.props.uri}
          formData={this.props.formData? this.props.formData : null}
          onChange={this.props.onChange}
          DropdownMenu={this.props.dropdownMenu}
          type={this.state.type}
          search={this.state.search}
          operator={this.state.operator}
          time={this.state.time}
          duration={this.state.duration}
          count={this.state.count}
          integral={this.state.integral}
          userType={this.state.userType}
          thirdType={this.state.thirdType}
          vipType={this.state.vipType}
          auditor={this.state.auditor}
          fans={this.state.fans}
          playCount={this.state.playCount}
          works={this.state.works}
          RefreshTableState={this.props.RefreshTableState}
        />
      </div>
    )
  }


}


export default SelectAndTable
