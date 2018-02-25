import React, { Component } from 'react'
import { Button, Menu, Dropdown, Icon} from 'antd'

import ModalTransfer from '../../modal/ModalTransfer'
import ModalScreen from '../../modal/ModalScreen'
import ModalDelete from '../../modal/ModalDelete'

//下拉菜单
class DropdownMenu extends React.Component {
  constructor(props){
    super(props)
     this.state={
       visible:false
     }
  }
  render() {
    const SuperType=this.props.SuperType
    const BatchType=this.props.BatchType //判断是否是批量操作
    return (
      <Dropdown overlay={
        <Menu className="dropMenu" style={{textAlign:"center"}}>
          <Menu.Item  key="1" style={{display:`${(SuperType=="screen")? "none" :
            `${SuperType=="sortNormal" ||SuperType=="sortDisable"? "none" : "block"}` }`
          }}>
            <ModalTransfer id={(BatchType == "BatchOperation")? this.props.SelectAll : this.props.record }
                visible={this.state.visible}  />
          </Menu.Item>
          <Menu.Item  key="2" style={{display:`${(SuperType=="censor")? "block" : "none"}`}}>
              <div><Button>待定</Button></div>
          </Menu.Item>
          <Menu.Item  key="3" style={{display:`${(SuperType=="hotCommon")? "block" : "none"}`}}>
              <div><Button>热门</Button></div>
          </Menu.Item>
          <Menu.Item  key="4" style={{display:`${(SuperType=="sortDisable")? "block" : "none"}`}}>
              <div><Button>启用</Button></div>
          </Menu.Item>
          <Menu.Item  key="5" style={{display:`${(SuperType=="screen")? "none" :
            `${SuperType=="sortNormal"? "none" : "block"}`}`
          }}>
            <ModalScreen id={(BatchType == "BatchOperation")? this.props.SelectAll : this.props.record }
              visible={this.state.visible} />
          </Menu.Item>
          <Menu.Item  key="6" style={{display:`${(SuperType=="screen")? "block" : "none"}`}}>
             <div><Button>解除</Button></div>
          </Menu.Item>
          <Menu.Item  key="7" style={{display:`${(SuperType=="screen")? "block" : "none"}`}}>
            <ModalDelete id={(BatchType == "BatchOperation")? this.props.SelectAll : this.props.record }
                shotImg={this.props.shotImg}
                BatchType={BatchType} />
          </Menu.Item>
          <Menu.Item  key="8" style={{display:`${(SuperType=="sortNormal")? "block" : "none"}`}}>
             <div><Button>向上</Button></div>
          </Menu.Item>
          <Menu.Item  key="9" style={{display:`${(SuperType=="sortNormal")? "block" : "none"}`}}>
             <div><Button>向下</Button></div>
          </Menu.Item>
          <Menu.Item  key="10" style={{display:`${(SuperType=="sortNormal")? "block" : "none"}`}}>
             <div><Button><span style={{color:"red"}}>停用</span></Button></div>
          </Menu.Item>
        </Menu>}
        trigger={['click']}>
              <Button type={(this.props.BatchType == "BatchOperation") ? "primary" : ""} >
                  {(this.props.BatchType == "BatchOperation") ? "批量操作" : "操作"}
                    <Icon type="down" />
              </Button>
      </Dropdown>
      )
  }

}

export default DropdownMenu
