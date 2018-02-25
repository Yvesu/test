import React, { Component } from 'react'
import { Menu } from 'antd'
import Fetch from 'utils/fetch'
class FilterMenu extends Component{
  constructor(props){
    super(props)
    this.state={
      selectKey:'',
      data:[]
    }
    // console.log(this.props);
  }
  handleClick = (e) => {
    // console.log('click ', e.key);
    this.setState({
      selectKey:e.key
    })
    if(this.props.isFilterShow){
      this.props.isFilterShow()
    }
    //播放量筛选
    if(this.props.changePlayCount){
      if(e.key==="0"){
        this.props.changePlayCount('')
      }else{
        this.props.changePlayCount(Number(e.key))
      }
    }
    //筛选文件
    if(this.props.changeFilterStatus){
      if(e.key==="null"){
        this.props.changeFilterStatus('')
      }else{
        this.props.changeFilterStatus(Number(e.key))
      }
    }
  }
  render(){

    return(
      <Menu
        mode="inline"
        defaultSelectedKeys={['null']}
        selectedKeys={[this.state.selectKey]}
        onClick={this.handleClick}

        className="opus_tabs_filter_menu"
      >
        {
          this.state.data.map((value,index)=>{
            return(
              <Menu.Item key={value.label}>{value.des}</Menu.Item>
            )
          })
        }

      </Menu>
    )
  }
  componentDidMount(){

    Fetch.post({
      uri:this.props.uri,
      callback:(res)=>{
        // console.log(res.data,'筛选菜单');
        this.setState({
          data:res.data,
        })
        //设置默认选中项
        const isNumber = isNaN(res.data[0].label)
        const isNull = res.data[0].label
        // console.log(isNumber===false);
        if(isNumber===false){
          if(isNull===null){
            const isNull = "null"
            this.setState({
              selectKey:isNull
            })
          }else{
            this.setState({
              selectKey:isNull.toString()
            })
          }
        }
      }
    })

  }
}

export default FilterMenu
